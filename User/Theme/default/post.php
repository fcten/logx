<?php display('inc/header.php'); ?>

<div class="grid_10" id="content">
	<div class="post">
		<h2 class="entry_title"><a href="<?php post_link() ?>"><?php post_title() ?></a></h2>
		<p class="entry_data">
			<span><?php _e('Author'); ?>：<?php post_author(); ?></span>
			<span><?php _e('Date'); ?>：<?php post_date('F j, Y'); ?></span>
			<?php if( is('Post') ): ?>
			<?php _e('Category'); ?>：<?php post_category(','); ?>
			<?php endif; ?>
		</p>
		<?php echo plugin_call('prePost'); ?>
		<?php post_content(); ?>
		<?php echo plugin_call('afterPost'); ?>
		<?php if( is('Post') ): ?>
		<p class="tags"><?php _e('Tags'); ?>: <?php post_tags(', ', true, 'none'); ?></p>
		<p class="prev"><?php _e('Prev Post'); ?>: <?php post_prev_post(); ?></p>
		<p class="next"><?php _e('Next Post'); ?>: <?php post_next_post(); ?></p>
		<?php endif; ?>
	</div>

	<?php display('inc/comments.php'); ?>

</div><!-- end #content-->

<?php display('inc/sidebar.php'); ?>
<?php display('inc/footer.php'); ?>
