<?php
// require the glimmer libraries
require_once('libs/glimmer.lib.php');
require_once('libs/glimmer.pages.lib.php');

// find the plugin directory
$pluginDir = dirname(__FILE__);
$pluginDir = dirname($pluginDir.'../');

// and parse the specfile
$spec = Glimmer::parseSpecfile($pluginDir . '/' . str_replace('.php', '.glimmer', $_GET['plugin']));

echo '<pre>', print_r($spec, true), '</pre>';

?>