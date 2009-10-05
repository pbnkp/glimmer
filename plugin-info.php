<?php
// require the glimmer libraries
require_once('libs/glimmer.lib.php');
require_once('libs/glimmer.pages.lib.php');

// find the plugin directory
$pluginDir = dirname(__FILE__);
$pluginDir = dirname($pluginDir.'../') . '/' . $_GET['plugin'];

// and parse the specfile
$spec = Glimmer::parseSpecfile($pluginDir . '/' . $_GET['plugin'] . '.spec');

// check dependencies
$status = Glimmer::checkStatus($_GET['plugin']);

?><html>

<head>
	
	<style>
		body { background: #fff; color: #333; line-height: 1.4em; font: 12px "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif; padding: 0; margin: 0; width: 659px; }
		
		.error, .warning, .ok { background: #d54e21; border-radius: 5px; color: #fff; font-weight: bold; margin-bottom: 10px; padding: 5px; text-align: center; -moz-border-radius: 5px; -webkit-border-radius: 5px; }
		.warning { background: #ffb500; }
		.ok { background: #308030; }
		
		.bold { font-weight: bold; }
		.normal { font-weight: normal; font-style: normal; }
		.italic { font-style: italic; }
		
		#content { float: left; padding: 5px 10px; width: 420px; }
		
		#sidebar { float: right; margin: 10px; width: 195px; }
		#sidebar .title { background: #cee1ef; font-weight: bold; padding: 5px; -moz-border-radius-topleft: 5px; -moz-border-radius-topright: 5px; }
		#sidebar .content { background: #eaf3fa; padding: 5px; -moz-border-radius-bottomleft: 5px; -moz-border-radius-bottomright: 5px;  }
		
		.clearfix { clear: both; }
	</style>
</head>

<body>
	<div id="content">
		content
	</div>
	
	
	<div id="sidebar">
		
		<div class="<?php echo $status['status']; ?>">
			<?php echo ($status['status'] == 'ok') ? 'OK' : ucwords($status['status']).'<br /><span class="normal">'.$status['message'].'</span>'; ?>
		</div>
		
		<div class="title">
			FYI
		</div>
		
		<div class="content">
			sidebar
		</div>
	</div>
	
	<div class="clearfix"></div>
</body>

</html>