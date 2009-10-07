<?php
// require the glimmer libraries
require_once('libs/glimmer.lib.php');
require_once('libs/glimmer.pages.lib.php');

// find the plugin directory
$pluginDir = dirname(__FILE__);
$pluginDir = dirname($pluginDir.'../');

// read the plugin details
$details = Glimmer::loadPlugins();

// parse the specfile
$spec = Glimmer::parseSpecfile($pluginDir . '/' . str_replace('.php', '.glimmer', $_GET['plugin']));

// and merge these together for ease of access
$plugin = array_merge($details[$_GET['plugin']], $spec);

// check dependencies
$status = Glimmer::checkStatus($_GET['plugin']);

?><html>

<head>
    
    <style>
        body { background: #fff; color: #333; line-height: 1.5em; font: 12px "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif; padding: 0; margin: 0; width: 679px; }
        
        a:link, a:visited { color: #21759b; text-decoration: none; }
        a:hover { color: #d54e21; }
        
        table { font-size: 12px; }
        th { text-align: right; }
        
        .error, .warning, .ok { background: #d54e21; border-radius: 5px; color: #fff; font-weight: bold; margin-bottom: 10px; padding: 5px 8px; -moz-border-radius: 5px; -webkit-border-radius: 5px; }
        .warning { background: #ffb500; }
        .ok { background: #308030; }
        
        .bold { font-weight: bold; }
        .normal { font-weight: normal; font-style: normal; }
        .italic { font-style: italic; }
        .align-center { text-align: center; }
        
        #content { float: left; padding: 5px 10px; width: 420px; }
        
        #sidebar { float: right; line-height: 1.5em; margin: 10px; width: 215px; }
        #sidebar .title { background: #cee1ef; font-weight: bold; padding: 5px; -moz-border-radius-topleft: 5px; -moz-border-radius-topright: 5px; }
        #sidebar .content { background: #eaf3fa; padding: 5px; -moz-border-radius-bottomleft: 5px; -moz-border-radius-bottomright: 5px;  }
        
        .clearfix { clear: both; }
    </style>
</head>

<body>
    <div id="content">
        
    </div>
    
    
    <div id="sidebar">
        
        <div class="<?php echo $status['status']; ?>">
            <?php echo ($status['status'] == 'ok') ? '<div class="align-center">OK</div>' : '<div class="align-center">'.ucwords($status['status']).'</div><span class="normal">'.$status['message'].'</span>'; ?>
        </div>
        
        <div class="title">
            Plugin Info
        </div>
        
        <div class="content">
            <table>
                <tr>
                    <th>Name:</th>
                    <td><a href="<?php echo $plugin['PluginURI']; ?>" target="_blank"><?php echo $plugin['Name']; ?></a></td>
                </tr>
                
                <tr>
                    <th>Version:</th>
                    <td><?php echo $plugin['Version']; ?></td>
                </tr>
                
                <tr>
                    <th>Author:</th>
                    <td><a href="<?php echo $plugin['AuthorURI']; ?>" target="_blank"><?php echo $plugin['Author']; ?></a></td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="clearfix"></div>
</body>

</html>