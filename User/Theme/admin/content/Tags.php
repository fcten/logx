<dl class="Tabs">
	<dt id="TagsTabs"><a href="#tabTagAdd"<?php if(!Request::G('page')): ?> class="select"<?php endif; ?>><?php if(!Request::G('mid')){ _e('Add'); }else{ _e('Edit'); } ?></a> <a href="#tabTagList"<?php if(Request::G('page')): ?> class="select"<?php endif; ?>><?php _e('List'); ?></a></dt>
	<dd id="tabTagAdd" style="display: <?php if(!Request::G('page')): ?>block<?php else: ?>none<?php endif; ?>; ">
<?php
if( $mid = Request::G('mid') ):
	$meta = new MetaLibrary();
	$meta->setMID( $mid );
	$m = $meta->getMeta();
	$m = $m[0];
?>
		<form action="<?php path(array('do'=>'editTag'),'AdminDo'); ?>" method="post" name="add_tag" id="add-tag">
			<ul id="add-post-option">
			<li>
				<label for="add-tag-title" class="add-post-label"><?php _e('Name'); ?></label>
				<p><input type="text" id="add-tag-title" name="name" value="<?php echo $m['name']; ?>" class="add-post-text" /></p>
			</li>
			<li>
				<label for="add-tag-alias" class="add-post-label"><?php _e('Alias'); ?></label>
				<p><input id="add-tag-alias" name="alias" type="text" value="<?php echo $m['alias']; ?>" class="add-post-text" autocomplete="off"></p>
			</li>
			<li>
				<label for="add-tag-description" class="add-post-label"><?php _e('Description'); ?></label>
				<p><input id="add-tag-description" name="description" type="text" value="<?php echo $m['description']; ?>" class="add-post-text" autocomplete="off"></p>
			</li>
			<li>
				<input type="hidden" name="mid" value="<?php echo $mid; ?>" />
				<input type="submit" id="add-tag-submit" value="<?php _e('Edit Tag'); ?>" />
			</li>
			</ul>
		</form>
<?php
else:
?>
		<form action="<?php path(array('do'=>'addTag'),'AdminDo'); ?>" method="post" name="add_tag" id="add-tag">
			<ul id="add-post-option">
			<li>
				<label for="add-tag-title" class="add-post-label"><?php _e('Name'); ?></label>
				<p><input type="text" id="add-tag-title" name="name" value="" class="add-post-text" /></p>
			</li>
			<li>
				<label for="add-tag-alias" class="add-post-label"><?php _e('Alias'); ?></label>
				<p><input id="add-tag-alias" name="alias" type="text" value="" class="add-post-text" autocomplete="off"></p>
			</li>
			<li>
				<label for="add-tag-description" class="add-post-label"><?php _e('Description'); ?></label>
				<p><input id="add-tag-description" name="description" type="text" value="" class="add-post-text" autocomplete="off"></p>
			</li>
			<li>
				<input type="submit" id="add-tag-submit" value="<?php _e('Add Tag'); ?>" />
			</li>
			</ul>
		</form>
<?php
endif;
?>
	</dd>
	<dd id="tabTagList" style="display: <?php if(Request::G('page')): ?>block<?php else: ?>none<?php endif; ?>; ">
		<p class="operate"><?php _e('Operation'); ?>: 
			<a href="#" onclick="checkAll('list-tag'); return false;"><?php _e('Check All'); ?></a> , 
			<a href="#" onclick="uncheckAll('list-tag'); return false;"><?php _e('Check None'); ?></a>&nbsp;&nbsp;&nbsp;
			<?php _e('Checked Item'); ?>: <a href="#" onclick="if(confirm('<?php _e('Sure to delete?'); ?>')){metaDelete();} return false;"><?php _e('Delete'); ?></a>
		</p>
		<table class="list-table" id="list-tag">
			<colgroup>
				<col width="25">
				<col width="200">
				<col width="250">
				<col width="155">
				<col width="130">
			</colgroup>
			<thead>
				<tr>
					<th class="radius-topleft"> </th>
					<th><?php _e('Name'); ?></th>
					<th><?php _e('Description'); ?></th>
					<th><?php _e('Alias'); ?></th>
					<th class="radius-topright"><?php _e('Reply'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$meta = new MetaLibrary();
			$meta->setType(2);
			$categories = $meta->getMeta();
			$i = 0;
			foreach( $categories as $c ) :
			?>
				<tr<?php if($i%2==0): ?> class="even"<?php endif; ?> id="tag-<?php echo $c['mid']; ?>">
					<td><input type="checkbox" value="<?php echo $c['mid']; ?>" name="mid[]"></td>
					<td><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManageTags'),'AdminDo'); ?>?mid=<?php echo $c['mid']; ?>');return false;"><?php echo $c['name']; ?></a></td>
					<td><?php echo $c['description']; ?></td>
					<td><?php echo $c['alias']; ?></td>
					<td><?php echo $c['reply']; ?></td>
				</tr>
			<?php
				$i ++;
			endforeach;
			?>
			</tbody>
		</table>
	</dd>
</dl>
<script>
Tabs(document.getElementById('TagsTabs'), 'select', <?php if(!Request::G('page')): ?>0<?php else: ?>1<?php endif; ?>);
$('#add-tag').ajaxForm({
beforeSubmit: function(){
	$("#add-tag-submit").attr('disabled',true);
},
success: function(data){
	data = eval('('+data+')');
	if( data.success ) {
		showMessage(data.message,'tips');
		setTimeout( "$('#h_Tag').click();", 3000);
	} else {
		showMessage(data.message,'error');
		$("#add-tag-submit").attr('disabled',false);
	}
}});

function metaDelete() {
	$("#list-tag input:checked").each(function(){
		$.post(
			'<?php path(array("do"=>"delMeta"),"AdminDo"); ?>',
			{'mid':$(this).val()},
			function(response){
				if(!response.success){
					showMessage(response.message,'error');
				}
			},
			"json"
		);
	});
	ajaxLoad('<?php path(array("do"=>"contentManageTags"),"AdminDo"); ?>?page=<?php echo Request::G("page")?Request::G("page"):1; ?>');
}
</script>
