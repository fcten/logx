<div class="box">
	<div class="box-title">
		<h3><?php _e('Plugin List'); ?></h3>
		<span></span>
	</div>
	<div class="box-content">
		<table class="list-table">
			<colgroup>
				<col width="200">
				<col width="380">
				<col width="80">
				<col width="80">
				<col width="150">
			</colgroup>
			<thead>
				<tr>
					<th class="radius-topleft"><?php _e('Name'); ?></th>
					<th><?php _e('Description'); ?></th>
					<th><?php _e('Version'); ?></th>
					<th><?php _e('Author'); ?></th>
					<th class="radius-topright"><?php _e('Operation'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$plugins = Plugin::getPlugins();
			$i = 0;
			foreach( $plugins as $plugin ) :
				$info = Plugin::getInfo( $plugin );
			?>
				<tr<?php if($i%2==0): ?> class="even"<?php endif; ?>>
					<td><?php echo $info['name']; ?></td>
					<td><?php echo $info['description']; ?></td>
					<td><?php echo $info['version']; ?></td>
					<td>
					<?php if($info['link']): ?>
						<a href="<?php echo $info['link']; ?>" target="_blank"><?php echo $info['author']; ?></a>
					<?php else: ?>
						<?php echo $info['author']; ?>
					<?php endif; ?>
					</td>
					<td>
					<?php if(Plugin::isInstall($plugin)): ?>
						<a href="#" onclick="pluginSetting('<?php echo $plugin; ?>');return false;"><?php _e('Setting'); ?></a>
						<a href="#" onclick="if(confirm('<?php _e('Sure to remove?'); ?>')){pluginRemove('<?php echo $plugin; ?>');}return false;"><?php _e('Remove'); ?></a>
					<?php else: ?>
						<a href="#" onclick="pluginInstall('<?php echo $plugin; ?>');return false;"><?php _e('Install'); ?></a>
					<?php endif; ?>
					</td>
				</tr>
			<?php
				$i ++;
			endforeach;
			?>
			</tbody>
		</table>
	</div>
</div>
<script>
function pluginInstall( plugin ) {
	$.post(
		'<?php path(array("do"=>"pluginInstall"),"AdminDo"); ?>',
		{'plugin':plugin},
		function(response){
			if(response.success){
				$('#h_Plugin').click();
			} else {
				showMessage(response.message,'error');
			}
		},
		"json"
	);
}
function pluginRemove( plugin ) {
	$.post(
		'<?php path(array("do"=>"pluginRemove"),"AdminDo"); ?>',
		{'plugin':plugin},
		function(response){
			if(response.success){
				$('#h_Plugin').click();
			} else {
				showMessage(response.message,'error');
			}
		},
		"json"
	);
}
function pluginSetting( plugin ) {
	$.post(
		'<?php path(array("do"=>"pluginSetting"),"AdminDo"); ?>',
		{'plugin':plugin},
		function(response){
			if(response.success){
				art.dialog({
					content:response.message,
					fixed:true,
					title:plugin+' <?php _e("Setting"); ?>',
					width:800,
					id:'pluginSetting'+plugin
				});
			} else {
				showMessage(response.message,'error');
			}
		},
		"json"
	);
}
</script>
