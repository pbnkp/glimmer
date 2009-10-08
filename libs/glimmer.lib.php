<?php
/**
 * Glimmer
 * The next-generation plugin manager for WordPress.
 * 
 * @package     Glimmer
 * @author      Matt Kirman <mattkirman@redflex.co.uk>
 * @copyright   2009 Matt Kirman, Redflex LLP
 * @license     http://github.com/mattkirman/glimmer/blob/master/LICENCE
 * @version     0.1.0-dev
 * @link        http://glimmer.redflex.co.uk/
 */

/**
 * Provides access to all basic Glimmer methods.
 * 
 * @package     Glimmer
 * @author      Matt Kirman <mattkirman@redflex.co.uk>
 * @copyright   2009 Matt Kirman, Redflex LLP
 * @license     http://github.com/mattkirman/glimmer/blob/master/LICENCE
 * @link        http://glimmer.redflex.co.uk/
 * @since       0.0.0
 */
class Glimmer
{
    
    protected static $wpdb;
    protected static $plugins = array();
    
    
    
    /**
     * Sets up the Glimmer environment. Also adds WP admin menu items and the
     * Glimmer dashboard.
     * 
     * @access public
     * @static
     * @return void
     **/
    public static function bootstrap()
    {
        // set up variables
        global $wpdb;
        self::$wpdb = $wpdb;
        
        // add admin menus, dashboard widget
        add_action('admin_menu', 'Glimmer::adminMenu');
        add_action('wp_dashboard_setup', 'Glimmer::dashboard');
        
        // add cron jobs
        add_action('glimmer__check_for_updates', 'Glimmer::checkForUpdates');
    }
    
    
    
    /**
     * Put all code that you want to execute on activation here
     *
     * @access  public
     * @static
     * @return  void
     **/
    public static function activationHook()
    {
        // check plugins for updates every hour
        wp_schedule_event(time(), 'hourly', 'glimmer__check_for_updates');
    }
    
    
    
    /**
     * Put all code that you want to execute on deactivation here
     *
     * @access  public
     * @static
     * @return  void
     **/
    public static function deactivationHook()
    {
        // disable hourly update checking
        wp_clear_scheduled_hook('glimmer__check_for_updates');
    }
    
    
    
    /**
     * Attaches the Glimmer menu to the WP admin.
     * 
     * @access public
     * @static
     * @return void
     **/
    public static function adminMenu()
    {
        $glimmerCount = '<span class="update-plugins"><span class="plugin-count">***</span></span>';
        $glimmerCount = null; # set $glimmerCount to null until we've added the method to check for plugins that need updating
        
        wp_enqueue_script( 'thickbox' );
        wp_enqueue_style( 'thickbox' );
        
        add_menu_page('Glimmer', 'Glimmer'.$glimmerCount, 8, 'glimmer', 'GlimmerPages::main');
        add_submenu_page('glimmer', 'Glimmer', 'Manage Plugins', 8, 'glimmer', 'GlimmerPages::main');
        //add_submenu_page('glimmer', 'Settings &lsaquo; Glimmer', 'Settings', 8, 'glimmer/settings', 'GlimmerPages::settings');
    }
    
    
    
    /**
     * Attaches the Glimmer dashboard to the admin home page
     * 
     * @access public
     * @static
     * @return void
     **/
    public static function dashboard()
    {
        wp_add_dashboard_widget('glimmer', 'Glimmer', 'GlimmerPages::dashboardWidget');
    }
    
    
    
    /**
     * Grabs all the Glimmer-enabled WP plugins.
     *
     * @access  public
     * @static
     * @return  array
     **/
    public static function loadPlugins()
    {
        // get all the WP plugins, this includes plugins that are disabled
        self::$plugins = self::getPlugins();
        
        $plugin_root = dirname(__FILE__);
        $plugin_root = dirname($plugin_root.'../');
        $plugin_root = dirname($plugin_root.'../');
        
        // for every plugin try and find a .glimmer specfile
        foreach (self::$plugins as $file => $data) {
            $dir = explode('/', $file);
            $specfile = $plugin_root.'/'.$dir[0].'/'.str_replace(' ','-',strtolower($data['Name'])).'.glimmer'; # this implementation is a bit flaky
            
            if (file_exists($specfile)){
                $spec = array_merge($data, self::parseSpecfile($specfile), array('_Glimmer' => true));
                self::$plugins[$file] = $spec;
            }
        }
        
        return self::$plugins;
        
    }
    
    
    
    /**
     * Parses the .glimmer specfile
     *
     * @access  public
     * @static
     * @param   string  $file   the specfile to load. Currently doesn't check
     *                          to see if the file actually exists
     * @return  array
     **/
    public static function parseSpecfile($file)
    {
        $spec = json_decode(file_get_contents($file), true);
        
        
        return $spec;
    }
    
    
    
    /**
     * Reads the plugin appcast for use when updating / installing the plugin
     *
     * @access  public
     * @static
     * @param   string $url     the appcast url to load.
     * @return  array or false on failure
     * 
     * @todo    Should probably cache the results so we don't hammer the appcast server
     **/
    public static function readAppcast($url)
    {
        if (!function_exists('curl_init')) { return false; }
        
        // create the cURL instance
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $buffer = curl_exec($ch);
        
        // we ought to check that we got a HTTP 200
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode != '200') {
            return false;
        }
        
        // we now need to convert appcast into an array
        $appcast = simplexml_load_string($buffer);
        
        
        return $appcast;
    }
    
    
    
    /**
     * Checks each plugin's appcast to see if it needs to be updated. Then stores
     * the results in the database for us to present to the user later.
     *
     * @access  public
     * @static
     * @return  void
     **/
    public static function checkForUpdates()
    {
        // first we need to get all plugins
        $plugins = self::loadPlugins();
        
        // and get the Glimmer plugin cache
        $pluginCache = self::readPluginCache();
        
        echo '<pre>', print_r($pluginCache, true), '</pre>';
        
        foreach ($plugins as $plugin) {
            if ($plugin['_Glimmer'] == true && $plugin['AppcastURI'] != null) {
                // right, this plugin is Glimmer capable so try to read it's Appcast
                $appcast = self::readAppcast($plugin['AppcastURI']);
                
                $newestVersion = $plugin['Version'];
                unset($pluginCache[$plugin['Name']]);
                
                foreach ($appcast->channel->item as $item) {
                    $glimmer = $item->enclosure->attributes('http://glimmer.redflex.co.uk/xml-namespaces/glimmer');
                    $enclosure = $item->enclosure->attributes();
                    
                    // if our version is newer than the appcast version we can safely ignore the update
                    if (version_compare($newestVersion, $glimmer->version, '>=')) { continue; }
                    
                    $pluginCache[$plugin['Name']] = array(
                        'title'         =>  (string) $item->title,
                        'pubDate'       =>  (string) $item->pubDate,
                        'description'   =>  (string) $item->description,
                        'downloadURL'   =>  (string) $enclosure->url,
                        'version'       =>  (string) $glimmer->version,
                        'signature'     =>  (string) $glimmer->signature,
                     );
                     
                     $newestVersion = $glimmer->version;
                }
            }
        }
        
        // and update the plugin cache
        self::updatePluginCache($pluginCache);
        
    }
    
    
    
    /**
     * Checks  whether all the plugin dependencies / incompatabilities for the
     * specified plugin are satisfied.
     *
     * @access  public
     * @static
     * @param   string  $lookupPlugin   the plugin to check
     * @return  array
     * 
     * @todo    Should probably use some sort of cache
     **/
    public static function checkStatus($lookupPlugin)
    {
        // find the plugin directory
        $pluginDir = dirname(__FILE__);
        $pluginDir = dirname($pluginDir.'../');
        $pluginDir = dirname($pluginDir.'../') . '/';
        
        // load all the plugins so we can check dependencies
        $plugins = self::getPlugins();
        
        
        // we need to get the name of the plugin we are looking up, not the ID
        $lookupPlugin_name = $plugins[$lookupPlugin]['Name'];
        
        
        // see if the plugin that we are checking has any dependencies
        $specfile = self::parseSpecfile($pluginDir.str_replace('.php', '.glimmer', $lookupPlugin));
        if (!array_key_exists('Dependencies', $specfile)) { return array('status' => 'ok'); }
        
        
        // trawl through each plugin to build the dictionary
        $pluginDict = array();
        foreach ($plugins as $plugin) {
            $pluginDict[$plugin['Name']] = $plugin;
        }
        
        
        // we now need to check that each dependency has been satisfied
        $errors = array();
        foreach ($specfile['Dependencies'] as $dep) {
            if (!array_key_exists($dep, $pluginDict)) {
                $errors[] = $dep;
            }
        }
        
        if (count($errors) > 0) {
            return array('status' => 'error', 'message' => 'These plugins are required:<br />&bull; '.implode('<br />&bull; ', $errors));   
        }
        
        
        // we now need to check that there are no incompatabilites
        $warnings = array();
        foreach ($specfile['Incompatabilities'] as $inc) {
            if (array_key_exists($inc, $pluginDict)) {
                $warnings[] = $inc;
            }
        }
        
        if (count($warnings) > 0) {
            return array('status' => 'warning', 'message' => 'This may not be compatible with:<br />&nbsp;&bull; '.implode('<br />&nbsp;&bull; ', $warnings));
        }
        
        
        return array('status' => 'ok');
        
    }
    
    
    
    /**
     * Because the WP functions are not always available we will store this
     * cache on disk rather than in the WP database.
     *
     * @access  protected
     * @static
     * @return  array or false
     **/
    protected static function readPluginCache()
    {
        // get the Glimmer root directory
        $glimmer_root = dirname(__FILE__);
        $glimmer_root = dirname($glimmer_root.'../');
        
        if (!file_exists($glimmer_root.'/__cache')) { self::createPluginCache(); return false; }
        
        return unserialize(file_get_contents($glimmer_root.'/__cache'));
    }
    
    
    
    /**
     * Updates the cache on disk. Currently rewrites the entire file _without_
     * preserving data.
     *
     * @access  protected
     * @static
     * @param   mixed   $data   The items to cache
     * @return  int or false
     **/
    protected function updatePluginCache($data)
    {
        // get the Glimmer root directory
        $glimmer_root = dirname(__FILE__);
        $glimmer_root = dirname($glimmer_root.'../');
        
        if (!file_exists($glimmer_root.'/__cache')) { self::createPluginCache(); }
        
        return file_put_contents($glimmer_root.'/__cache', serialize($data));
    }
    
    
    
    /**
     * Creates the plugin cache file, the cache must then be updated with
     * Glimmer::updatePluginCache()
     *
     * @access  protected
     * @static
     * @return  bool
     **/
    protected static function createPluginCache()
    {
        // get the Glimmer root directory
        $glimmer_root = dirname(__FILE__);
        $glimmer_root = dirname($glimmer_root.'../');
        
        if (file_exists($glimmer_root.'/__cache')) { return false; }
        
        $fh = fopen($glimmer_root.'/__cache', 'w');
        fclose($fh);
        
        return true;
    }
    
    
    
    /**
     * A replacement for the default Wordpress get_plugins(). For use when we
     * haven't got access to the Wordpress codebase.
     *
     * @access  protected
     * @static
     * @return  array
     **/
    protected static function getPlugins()
    {
        if (function_exists('get_plugins')) {
            return get_plugins();
        }
        
        $wp_plugins = array ();
        $plugin_root = dirname(__FILE__);
        $plugin_root = dirname($plugin_root.'../');
        $plugin_root = dirname($plugin_root.'../');

        // Files in wp-content/plugins directory
        $plugins_dir = @ opendir( $plugin_root);
        $plugin_files = array();
        if ( $plugins_dir ) {
            while (($file = readdir( $plugins_dir ) ) !== false ) {
                if ( substr($file, 0, 1) == '.' )
                    continue;
                if ( is_dir( $plugin_root.'/'.$file ) ) {
                    $plugins_subdir = @ opendir( $plugin_root.'/'.$file );
                    if ( $plugins_subdir ) {
                        while (($subfile = readdir( $plugins_subdir ) ) !== false ) {
                            if ( substr($subfile, 0, 1) == '.' )
                                continue;
                            if ( substr($subfile, -4) == '.php' )
                                $plugin_files[] = "$file/$subfile";
                        }
                    }
                } else {
                    if ( substr($file, -4) == '.php' )
                        $plugin_files[] = $file;
                }
            }
        }
        @closedir( $plugins_dir );
        @closedir( $plugins_subdir );

        if ( !$plugins_dir || empty($plugin_files) )
            return $wp_plugins;

        foreach ( $plugin_files as $plugin_file ) {
            if ( !is_readable( "$plugin_root/$plugin_file" ) )
                continue;
            
            $plugin_data = self::getPluginData("$plugin_root/$plugin_file");

            if ( empty ( $plugin_data['Name'] ) )
                continue;

            $wp_plugins[$plugin_file] = $plugin_data;
        }
        
        return $wp_plugins;
    }
    
    
    
    /**
     * A replacement for the default Wordpress get_plugin_data(). For use when
     * we haven't got access to the Wordpress codebase.
     *
     * @access  protected
     * @static
     * @return  array
     **/
    protected static function getPluginData($plugin_file)
    {
        // We don't need to write to the file, so just open for reading.
        $fp = fopen($plugin_file, 'r');

        // Pull only the first 8kiB of the file in.
        $plugin_data = fread( $fp, 8192 );

        // PHP will close file handle, but we are good citizens.
        fclose($fp);

        preg_match( '|Plugin Name:(.*)$|mi', $plugin_data, $name );
        preg_match( '|Plugin URI:(.*)$|mi', $plugin_data, $uri );
        preg_match( '|Version:(.*)|i', $plugin_data, $version );
        preg_match( '|Description:(.*)$|mi', $plugin_data, $description );
        preg_match( '|Author:(.*)$|mi', $plugin_data, $author_name );
        preg_match( '|Author URI:(.*)$|mi', $plugin_data, $author_uri );
        preg_match( '|Text Domain:(.*)$|mi', $plugin_data, $text_domain );
        preg_match( '|Domain Path:(.*)$|mi', $plugin_data, $domain_path );

        $plugin_data = array(
                    'Name' => $name, 'Title' => $name, 'PluginURI' => $uri, 'Description' => $description,
                    'Author' => $author_name, 'AuthorURI' => $author_uri, 'Version' => $version,
                    'TextDomain' => $text_domain, 'DomainPath' => $domain_path
                    );
        
        foreach ($plugin_data as $key => $value) {
            $plugin_data[$key] = trim($value[1]);
        }
        
        return $plugin_data;
    }
    
}

?>