<div class="box clearfix">
	<div class="box-title">
		<h3><?php _e('Current Theme'); ?></h3>
		<span></span>
	</div>
	<div class="box-content">
		<dl class="box-theme current-theme">
		<?php
		$default = Theme::getDefaultTheme();
		$info = Theme::getInfo( $default );
		?>
			<dt><img src="<?php echo $info['screenshot']; ?>"></dt>
			<dd class="theme_name"><?php echo $info['name']; ?></dd>
			<dd><?php _e('Author'); ?>：<?php echo $info['author']; ?></dd>
			<dd><?php _e('Version'); ?>：<?php echo $info['version']; ?></dd>
			<dd><?php _e('Link'); ?>：<a href="<?php echo $info['link']; ?>" target="_blank"><?php echo $info['link']; ?></a></dd>
			<dd><?php _e('Description'); ?>：<?php echo $info['description']; ?></dd>
		</dl>
	</div>
</div>

<div class="box clearfix">
	<div class="box-title">
		<h3><?php _e('Available Themes'); ?></h3>
		<span></span>
	</div>
	<div class="box-content">
	<?php
	$themes = Theme::getAllThemes();
	foreach( $themes as $theme ):
		if( $theme == $default ) {
			continue;
		}
		$info = Theme::getInfo( $theme );
	?>
		<dl class="box-theme">
			<dt><a href="#" onclick="setTheme('<?php echo $theme; ?>'); return false;" title="应用该主题"><img src="<?php echo $info['screenshot']; ?>"></a></dt>
			<dd class="theme_name"><?php echo $info['name']; ?></dd>
			<dd><?php _e('Author'); ?>：<?php echo $info['author']; ?></dd>
			<dd><?php _e('Version'); ?>：<?php echo $info['version']; ?></dd>
			<dd><?php _e('Link'); ?>：<a href="<?php echo $info['link']; ?>" target="_blank"><?php echo $info['link']; ?></a></dd>
			<dd><?php _e('Description'); ?>：<?php echo $info['description']; ?></dd>
		</dl>
	<?php
	endforeach;
	?>
	</div>
</div>
<script>
function setTheme( theme ) {
	$.post(
		'<?php path(array("do"=>"setTheme"),"AdminDo"); ?>',
		{'theme':theme},
		function(response){
			if(response.success){
				$('#h_Theme').click();
			} else {
				showMessage(response.message,'error');
			}
		},
		"json"
	);
}
</script>
