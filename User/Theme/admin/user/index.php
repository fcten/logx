<?php
if( !( $uid = Request::G('uid') ) ){
?>
<div class="box">
	<div class="box-title">
		<h3><?php _e('User List'); ?></h3>
		<span></span>
	</div>
	<div class="box-content">
		<table class="list-table">
			<colgroup>
				<col width="25">
				<col width="200">
				<col width="250">
				<col width="250">
				<col width="165">
			</colgroup>
			<thead>
				<tr>
					<th class="radius-topleft"> </th>
					<th><?php _e('Username'); ?></th>
					<th><?php _e('Email'); ?></th>
					<th><?php _e('Website'); ?></th>
					<th class="radius-topright"><?php _e('Group'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$i = 0;
			$user = new UserLibrary();
			$user->setPerPage( 10 );
			$user->setCurrentPage( 1 );
			$users = $user->getUsers();
			foreach( $users as $u ) :
			?>
				<tr<?php if($i%2==0): ?> class="even"<?php endif; ?> id="user-<?php echo $u['uid']; ?>">
					<td><input type="checkbox" value="1" name="uid[]"></td>
					<td><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'user'),'AdminDo'); ?>?uid=<?php echo $u['uid']; ?>');return false;"><?php echo $u['username']; ?></a></td>
					<td><?php echo $u['email']; ?></td>
					<td><?php echo $u['website']; ?></td>
					<td><?php if( $u['group'] == 10 ) { echo _t('Admin'); } elseif( $u['group'] == 5 ) { echo _t('Editor'); } else { echo _t('Guest'); } ?></td>
				</tr>
			<?php
				$i ++;
			endforeach;
			?>
			</tbody>
		</table>
		<?php
		$nav = $user->nav();
		if( $nav ) :
		?>
		<div class="nav">
			<div class="nav-content">
				<ul>
				<?php for( $i=1 ; $i<=$nav['totalPage'] ; $i ++ ): ?>
					<li<?php if($i==$nav['currentPage']):?> class="current"<?php endif; ?>><a href=""><?php echo $i; ?></a></li>
				<?php endfor; ?>
				</ul>
			</div>
		</div>
		<?php
		endif;
		?>
	</div>
</div>
<?php
} else {
	$u = Widget::getWidget('User')->getUser( $uid );
?>
<div class="box">
	<div class="box-title">
		<h3><?php _e('User Edit'); ?></h3>
		<span></span>
	</div>
	<div class="box-content">
		<form action="<?php path(array('do'=>'editUser'),'AdminDo'); ?>" method="post" name="edit_user" id="edit-user">
			<ul id="add-post-option">
			<li>
				<label for="edit-user-name" class="add-post-label"><?php _e('Username'); ?></label>
				<p><h2><?php echo $u['username']; ?></h2></p>
			</li>
			<li>
				<label for="edit-user-pw" class="add-post-label"><?php _e('Password'); ?></label>
				<p><input id="edit-user-pw" name="password" type="password" value="" class="add-post-text" autocomplete="off"></p>
				<p><?php _e('Leave it blank if you do not want password be changed.'); ?></p>
			</li>
			<li>
				<label for="edit-user-email" class="add-post-label"><?php _e('Email'); ?></label>
				<p><input id="edit-user-email" name="email" type="text" value="<?php echo $u['email']; ?>" class="add-post-text" autocomplete="off"></p>
			</li>
			<li>
				<label for="edit-user-group" class="add-post-label"><?php _e('Website'); ?></label>
				<p><input id="edit-user-website" name="website" type="text" value="<?php echo $u['website']; ?>" class="add-post-text" autocomplete="off"></p>
			</li>
			<li>
				<input type="hidden" name="uid" value="<?php echo $uid; ?>" />
				<input type="submit" id="edit-user-submit" value="<?php _e('Edit User'); ?>" />
			</li>
			</ul>
		</form>
	</div>
</div>
<script>
$('#edit-user').ajaxForm({
beforeSubmit: function(){
	$("#edit-user-submit").attr('disabled',true);
},
success: function(data){
	data = eval('('+data+')');
	if( data.success ) {
		showMessage(data.message,'tips');
		setTimeout( "$('#h_User').click();", 3000);
	} else {
		showMessage(data.message,'error');
		$("#edit-user-submit").attr('disabled',false);
	}
}});
</script>
<?php
}
