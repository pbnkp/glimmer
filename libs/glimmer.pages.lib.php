<?php
/**
 * Handles all content for Glimmer.
 * 
 * @package Glimmer
 * @subpackage GlimmerPages
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
		
		$pluginCountAll = number_format(count(self::$plugins));
		
		echo <<<EOF
<script type="text/javascript" src="/wp-content/plugins/glimmer/js/main.js"></script>		

		<div class="wrap">
			<div class="icon32" id="icon-plugins"><br /></div>
			<h2>Glimmer</h2>
			
			<ul class="subsubsub">
				<li><a href="#" class="current">All <span class="count">($pluginCountAll)</span></a></li>
			</ul>
			
			<table id="all-plugins-table" class="widefat" cellspacing="0">
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
			$packageDir = explode('/', $package);
			$packageDir = $packageDir[0];
			
			$name = $data['Name'];
			$description = $data['Description'];
			$version = $data['Version'];
			$author = $data['Author'];
			$authorURI = $data['AuthorURI'];
			$pluginURI = $data['PluginURI'];
			
			$status = self::checkStatus($packageDir);
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
							<div class="row-actions-visible">
EOF;
			
			if ($data['PackageName'] != 'com.redflex.glimmer') {
				echo '<span class="0"><a href="'.$actionURL.'" class="background">'.$action.'</a> |</span>';
			}
			
			echo <<<EOF
								<span class="1"><a href="#">Check for updates</a></span>
							</div>
						</td>
						<td class="desc">
							<a href="/wp-content/plugins/glimmer/plugin-info.php?plugin=$packageDir&TB_iframe=true&width=640&height:560" class="thickbox onclick" title="$name">Plugin Info</a>
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