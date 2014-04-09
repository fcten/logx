<div class="grid_4" id="sidebar">
	<?php echo plugin_call('preSideBar'); ?>

	<?php
	$widgets = widget_get_all();
	foreach( $widgets as $widget ) :
	?>
	<div class="widget">
		<h3><?php widget_name( $widget ); ?></h3>
		<ul>
			<?php widget_content( $widget ); ?>
		</ul>
	</div>
	<?php endforeach; ?>

	<div class="widget">
		<h3><?php _e('Others'); ?></h3>
		<ul>
		<?php if(user_is_login()): ?>
			<?php if(user_is_editor()): ?>
			<li class="last"><a href="<?php path(array(),'Admin'); ?>"><?php _e('Admin Panel'); ?> (<?php user_name(); ?>)</a></li>
			<?php endif; ?>
			<li><a href="<?php path(array('do'=>'logout'),'Action'); ?>"><?php _e('Logout'); ?></a></li>
		<?php else: ?>
			<li class="last"><a href="<?php path(array(),'Admin'); ?>"><?php _e('Login'); ?></a></li>
		<?php endif; ?>
			<li><a href="http://validator.w3.org/check/referer">Valid XHTML</a></li>
			<li><a href="http://www.logx.org">LogX</a></li>
		</ul>
	</div>

	<?php echo plugin_call('afterSideBar'); ?>
</div><!-- end #sidebar -->
