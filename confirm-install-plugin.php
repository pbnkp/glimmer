<?php
// require the glimmer libraries
require_once('libs/glimmer.lib.php');
require_once('libs/glimmer.pages.lib.php');

$plugin = $_GET['plugin'];

// find the plugin directory
$pluginDir = dirname(__FILE__);
$pluginDir = dirname($pluginDir.'../');

// read the plugin details
$details = Glimmer::loadPlugins();

// parse the specfile
$spec = Glimmer::parseSpecfile($pluginDir . '/' . str_replace('.php', '.glimmer', $plugin));

// and merge these together for ease of access
$plugin = array_merge($details[$plugin], $spec);

$pluginUpdate = Glimmer::readPluginCache();
$pluginUpdate = $pluginUpdate[$plugin['Name']];

?><div class="update-popup">
    <div class="title">
        <h2>A new version of <?php echo $plugin['Name']; ?> is available!</h2>
        <p>
            <?php echo $plugin['Name']; ?> <?php echo $pluginUpdate['version']; ?> is available
            &ndash; you have <?php echo $plugin['Version']; ?>. Would you like to install it now?
        </p>
    </div>

    <div class="release-notes">
        <h2>Release Notes:</h2>
        
        <div class="notes">
            <?php echo $pluginUpdate['description']; ?>
            
        </div>
    </div>
</div>