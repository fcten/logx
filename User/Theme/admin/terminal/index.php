<!--<div class="box box-small">
	<div class="box-title">
		<h3><?php _e('Pending Processing'); ?></h3>
		<span></span>
	</div>
	<div class="box-content">
	</div>
</div>-->
<div class="box box-small">
	<div class="box-title">
		<h3><?php _e('Latest Comments'); ?></h3>
		<span></span>
	</div>
	<div class="box-content">
		<ol>
		<?php
		$comments = Database::fetchAll( 'SELECT * FROM `' . DB_PREFIX . 'comments` ORDER BY `cid` DESC limit 0,15' );
		foreach( $comments as $c ):
		?>
			<li><a href="<?php echo Router::patch('Post',array('pid'=>$c['pid'])); ?>#comment-<?php echo $c['cid']; ?>"><?php echo $c['author']; ?></a>: <?php echo str_replace( array('<br>', '<br />', '<br/>'), '', $c['content'] ); ?></li>
		<?php
		endforeach;
		?>
		</ol>
	</div>
</div>
<div class="box box-small">
	<div class="box-title">
		<h3><?php _e('Official Information'); ?></h3>
		<span></span>
	</div>
	<div class="box-content" id="official-infomation">
		Loading ...
	</div>
	<script>
		var CVersion = '<?php echo LOGX_VERSION; ?>';
		$.getScript( "http://update.logx.org/update.php" );
	</script> 
</div>
<div class="box box-small">
	<div class="box-title">
		<h3><?php _e('Server Infomation'); ?></h3>
		<span></span>
	</div>
	<div class="box-content">
		<?php _e('Used Disk Space'); ?>: <?php echo round( LogX::countDirSize( LOGX_ROOT )/(1024*1024), 2 ); ?>MB
		<br /><?php _e('Run Time Limit'); ?>: <?php echo get_cfg_var('max_execution_time') ?>s
		<br /><?php _e('Operating System'); ?>: <?php echo LogX::$_globalVars['SYSTEM']['OS']; ?>
		<br /><?php _e('PHP Version'); ?>: <?php echo LogX::$_globalVars['SYSTEM']['PHP']; ?>
		<br /><?php _e('MySQL Version'); ?>: <?php echo LogX::$_globalVars['SYSTEM']['MYSQL']; ?>
		<br /><?php _e('HTTP Server'); ?>: <?php echo LogX::$_globalVars['SYSTEM']['HTTP']; ?>
	</div>
</div>
