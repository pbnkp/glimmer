<?php
/**
 * Glimmer
 * The next-generation plugin manager for WordPress.
 * 
 * @package     Glimmer
 * @author      Matt Kirman <mattkirman@redflex.co.uk>
 * @copyright   2009 Matt Kirman, Redflex LLP
 * @license     http://github.com/mattkirman/glimmer/blob/master/LICENCE
 * @version     0.0.0
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
        $plugins = self::getPlugins();
        
        // for every plugin try and find a .glimmer specfile
        foreach ($plugins as $file => $data) {
            $dir = explode('/', $file);
            $specfile = ABSPATH.'wp-content/plugins/'.$dir[0].'/'.str_replace(' ','-',strtolower($data['Name'])).'.glimmer'; # this implementation is a bit flaky
            
            if (file_exists($specfile)){
                $spec = array_merge($data, self::parseSpecfile($specfile));
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
        
        // Load all the plugins so we can check dependencies
        $plugins = self::getPlugins();
        
        foreach ($plugins as $key => $value) {
            $dir = explode('/', $key);
            $specfile = $pluginDir.$dir[0].'/'.str_replace(' ','-',strtolower($value['Name'])).'.glimmer';
            
            if (file_exists($specfile)){
                $spec = array_merge($value, self::parseSpecfile($specfile));
                $plugins[$key] = $spec;
            }
        }
        
        // see if the plugin that we are checking has any dependencies
        $specfile = self::parseSpecfile($pluginDir.str_replace('.php', '.glimmer', $lookupPlugin));
        if (!array_key_exists('Dependencies', $specfile)) { return array('status' => 'ok'); }
        
        // trawl through each plugin to build the dictionary
        $pluginDict = array();
        foreach ($plugins as $plugin) {
            if (array_key_exists('PackageName', $plugin) && $plugin['PackageName'] != $specfile['PackageName']) {
                $pluginDict[$plugin['PackageName']] = $plugin;
            }
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
            return array('status' => 'warning', 'message' => 'There may be compatability issues with:<br />&bull; '.implode('<br />&bull; ', $warnings));
        }
        
        
        return array('status' => 'ok');
        
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