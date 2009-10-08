<?php
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
        $pluginCache = Glimmer::readPluginCache();
        
        echo <<<EOF
<script type="text/javascript" src="/wp-content/plugins/glimmer/js/main.js"></script>
        
        <style>
            .update-message { background-color: #fffbe4; border: 1px solid #dfdfdf; color: #000; display: block; float: right; font-weight: bold; margin-top: -3px; padding: 3px 5px; -moz-border-radius: 5px; }
            .plugins .second td, .plugins .second th { padding: 3px 7px 5px; }
            .align-right { text-align: right; }
            
            #TB_ajaxContent { background: #f9f9f9; }
            
            .update-popup { position: relative; height: 100%; }
            .update-popup .title { height: 70px; }
            .update-popup .title h2 { margin: 0 0 5px; padding-top: 10px; }
            .update-popup .title p { margin: 0; }
            .update-popup .release-notes { bottom: 30px; height: 255px; position: absolute; width: 520px; }
            .update-popup .release-notes h2 { font-size: 12px; margin: 0 0 5px; }
            .update-popup .release-notes .notes { background: #fff; border: 1px solid #ddd; bottom: 0; height: 220px; overflow-x: hidden; overflow-y: scroll; padding: 5px; position: absolute; width: 508px; }
            .update-popup .buttons { bottom: -5px; height: 20px; position: absolute; text-align: right; width: 520px; }
            .update-popup .buttons a.button {  }
        </style>
        
        <div class="wrap">
            <div class="icon32" id="icon-plugins"><br /></div>
            <h2>Glimmer</h2>
            
            <p class="align-right">
                &nbsp;<a href="/wp-content/plugins/glimmer/check-for-updates.php" class="background button">Check for updates</a>
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
EOF;
            
           if (array_key_exists($data['Name'], $pluginCache)) { 
               $versionNumber = $pluginCache[$data['Name']]['version'];
               
                echo <<<EOF
                            <span class="update-message">
                                <a href="/wp-content/plugins/glimmer/confirm-install-plugin.php?plugin=$package&width=520&height=360" class="thickbox onclick" title="Plugin Update">A new version of $name is available</a>
                            </span>
EOF;
            }
            
            echo <<<EOF
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