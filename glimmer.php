<?php
/*
Plugin Name: Glimmer
Plugin URI: http://glimmer.redflex.co.uk/
Description: The next-generation plugin manager for WordPress.
Version: 0.0.0
Author: Matt Kirman
Author URI: http://mattkirman.com/
*/

require_once('libs/glimmer.lib.php');
require_once('libs/glimmer.pages.lib.php');

Glimmer::bootstrap();

register_activation_hook(__FILE__, 'Glimmer::activationHook');
register_deactivation_hook(__FILE__, 'Glimmer::deactivationHook');

?>