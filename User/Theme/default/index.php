<?php
/**
 * @name LogX Default Theme
 * 
 * @description 这是 LogX 系统的一套默认皮肤
 * @screenshot screenshot.png
 * @author fcten
 * @version 1.0.0
 * @link http://logx.org/
 */

display('inc/header.php');
?>
	<div class="grid_10" id="content">
		<?php echo plugin_call('prePostList'); ?>
		<?php while(post_next()): ?>
		<div class="post">
			<h2 class="entry_title"><a href="<?php post_link(); ?>"><?php post_title() ?></a></h2>
			<p class="entry_data">
				<span><?php _e('Author'); ?>：<?php post_author(); ?></span>
				<span><?php _e('Date'); ?>：<?php post_date('F j, Y'); ?></span>
				<span><?php _e('Category'); ?>：<?php post_category(); ?></span>
				<a href="<?php post_link(); ?>#comments"><?php post_comment('No Comments', '1 Comment', '%d Comments'); ?></a>
			</p>
			<?php post_content(500); ?>
		</div>
		<?php endwhile; ?>

		<?php post_nav(); ?>
	</div><!-- end #content-->

<?php
display('inc/sidebar.php');
display('inc/footer.php');
?>
