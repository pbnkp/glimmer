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
 * Handles Glimmer page content.
 * 
 * @package     Glimmer
 * @author      Matt Kirman <mattkirman@redflex.co.uk>
 * @copyright   2009 Matt Kirman, Redflex LLP
 * @license     http://github.com/mattkirman/glimmer/blob/master/LICENCE
 * @link        http://glimmer.redflex.co.uk/
 * @since       0.0.0
 * 
 * @todo        Should page content be moved to a flat file rather than a class?
 */
class GlimmerPages extends Glimmer
{
    
    public static function dashboardWidget()
    {
        echo '<small>Loading&hellip;</small>';
    }
    
    
    
    public static function main()
    {
        Glimmer::loadPlugins();
        
        echo <<<EOF
<script type="text/javascript" src="/wp-content/plugins/glimmer/js/main.js"></script>
        
        <style>
            .update-message { background-color: #fffbe4; border: 1px solid #dfdfdf; color: #000; font-weight: bold; padding: 3px 5px; -moz-border-radius: 5px; }
            .plugins .second td, .plugins .second th { padding: 3px 7px 5px; }
            .align-right { text-align: right; }
        </style>
        
        <div class="wrap">
            <div class="icon32" id="icon-plugins"><br /></div>
            <h2>Glimmer</h2>
            
            <p class="align-right">
                &nbsp;<a href="/wp-content/plugins/glimmer/check-for-updates.php" class="background">Check for updates</a>
            </p>
            
            <table id="all-plugins-table" class="widefat" cellspacing="0" style="margin:15px 0">
                <thead>
                    <tr>
                        <th style="width:3px;padding:0"></th>
                        <th class="manage-column" scope="col">Plugin</th>
                        <th class="manage-column" scope="col">Description</th>
                    </tr>
                </thead>
                <tfoot>
                
                    <tr>
                        <th style="width:3px;padding:0"></th>
                        <th class="manage-column" scope="col">Plugin</th>
                        <th class="manage-column" scope="col">Description</th>
                    </tr>
                </tfoot>
                
                <tbody class="plugins">
EOF;
        
        foreach (self::$plugins as $package => $data) {
            // if the plugin is not Glimmer aware, skip it
            if ($data['_Glimmer'] != true) { continue; }
            
            $packageDir = explode('/', $package);
            $packageDir = $packageDir[0];
            
            $name = $data['Name'];
            $description = $data['Description'];
            $version = $data['Version'];
            $author = $data['Author'];
            $authorURI = $data['AuthorURI'];
            $pluginURI = $data['PluginURI'];
            
            $status = self::checkStatus($package);
            $statusClass = 'green';
            if ($status['status'] == 'warning') {
                $statusClass = 'orange';
            } elseif ($status['status'] == 'error') {
                $statusClass = 'red';
            }
            
            $is_active = is_plugin_active($package);
            $class = $is_active ? 'active' : 'inactive';
            
            $action = $is_active ? 'deactivate' : 'activate';
            $actionURL = wp_nonce_url('plugins.php?action='.$action.'&amp;plugin=' . $package, $action . '-plugin_' . $package);
            $action = ucwords($action);
            
            echo <<<EOF
                    <tr class="$class">
                        <td style="width:3px;padding:0;background:$statusClass"></td>
                        <td class="plugin-title"><strong>$name</strong></td>
                        <td class="desc"><p>$description</p></td>
                    </tr>
                    <tr class="second $class">
                        <td style="width:3px;padding:0;background:$statusClass"></td>
                        <td class="plugin-title">
                            <div class="row-actions-visible controls">
EOF;
            
            if ($data['Name'] != 'Glimmer') {
                echo '<span class="0"><a href="'.$actionURL.'" class="background">'.$action.'</a>&nbsp;</span>';
            }
            
            echo <<<EOF
                            </div>
                        </td>
                        <td class="desc controls">
                            <a href="/wp-content/plugins/glimmer/plugin-info.php?plugin=$package&TB_iframe=true&width=670&height=460" class="thickbox onclick" title="$name">Plugin Info</a>
                            
                            <span class="update-message">
                                Version *** is available. <a href="#" class="background">Update now</a>
                            </span>
                        </td>
                    </tr>
EOF;
            
        }
        
        echo <<<EOF
                
                </tbody>
            </table>
        </div>
EOF;
        
    }
    
    
    
    public static function settings()
    {
        
        
        echo <<<EOF
<div class="wrap">
            <div class="icon32" id="icon-plugins"><br /></div>
            <h2>Glimmer &rsaquo; Settings</h2>
            
EOF;
        
        
        
        echo <<<EOF
    
        </div>
EOF;
        
    }
    
}

?>