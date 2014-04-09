<?php display('inc/header.php'); ?>

<div class="grid_10" id="content">

	<?php echo plugin_call('prePage'); ?>

	<div class="post">
		<?php page_content(); ?>
	</div>

	<?php echo plugin_call('afterPage'); ?>

</div><!-- end #content-->

<?php display('inc/sidebar.php'); ?>
<?php display('inc/footer.php'); ?>
