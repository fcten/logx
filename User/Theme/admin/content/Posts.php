<dl class="Tabs">
	<dt id="PostsTabs"><a href="#tabPostAdd"<?php if(!Request::G('page')): ?> class="select"<?php endif; ?>><?php if(!Request::G('pid')){ _e('Add'); }else{ _e('Edit'); } ?></a> <a href="#tabPostList"<?php if(Request::G('page')): ?> class="select"<?php endif; ?>><?php _e('List'); ?></a></dt>
	<dd id="tabPostAdd" style="display: <?php if(!Request::G('page')): ?>block<?php else: ?>none<?php endif; ?>; ">
<?php
if( $pid = Request::G('pid') ):
	$post = new PostLibrary();
	$p = $post->getPost( $pid );
?>
		<form action="<?php path(array('do'=>'editPost'),'AdminDo'); ?>" method="post" name="add_post" id="add-post">
			<div id="add-post-right">
				<ul id="add-post-option">
				<li>
					<label for="add-post-title" class="add-post-label"><?php _e('Title'); ?></label>
					<p><input type="text" id="add-post-title" name="title" value="<?php echo $p['title']; ?>" class="add-post-text" /></p>
				</li>
				<li>
					<label class="add-post-label"><?php _e('Categories'); ?></label>
					<ul class="clearfix">
					<?php
					$meta = new MetaLibrary();
					$meta->setType(1);
					$categories = $meta->getMeta( FALSE );
					$meta->setPID( $pid );
					$pcs = $meta->getMeta( FALSE );
					$temp = array();
					foreach( $pcs as $pc ) {
						$temp[] = $pc['mid'];
					}

					foreach( $categories as $c ) {
						if( !Widget::getWidget('User')->checkPrivilege( 'POST', $c['mid'] ) ) {
							continue;
						}
					?>
						<li><input type="checkbox" id="category-<?php echo $c['mid']; ?>" value="<?php echo $c['mid']; ?>" name="category[]" <?php if( in_array( $c['mid'], $temp ) ): ?> checked="true"<?php endif; ?>>
						<label for="category-<?php echo $c['mid']; ?>"><?php echo $c['name']; ?></label></li>
					<?php
					}
					?>
					</ul>
				</li>
				<li>
					<label class="add-post-label"><?php _e('Privilege'); ?></label>
					<ul class="clearfix">
						<li><input id="add-post-allowComment" name="allowComment" type="checkbox" value="1"<?php if( $p['allow_reply'] ): ?> checked="true"<?php endif; ?>>
						<label for="add-post-allowComment"><?php _e('Allow Comment'); ?></label></li>
						<li><input id="add-post-top" name="top" type="checkbox" value="1"<?php if( $p['top'] ): ?> checked="true"<?php endif; ?>>
						<label for="add-post-top"><?php _e('Top Post'); ?></label></li>
					</ul>
				</li>
				<li>
					<?php
					$meta = new MetaLibrary();
					$meta->setType(2);
					$meta->setPID( $pid );
					$tags = $meta->getMeta();
					$t = '';
					foreach( $tags as $tag ) {
						$t .= $tag['name'].',';
					}
					$t = substr( $t, 0, strlen( $t )-1 );
					?>
					<label for="add-post-tags" class="add-post-label"><?php _e('Tags'); ?></label>
					<p><input id="add-post-tags" name="tags" type="text" value="<?php echo $t; ?>" class="add-post-text" autocomplete="off"></p>
				</li>
				<li>
					<label class="add-post-label"><?php _e('Attachment'); ?></label> <a href="#" onclick="uploadPanel(); return false;" style="font-size:12px;"><?php _e('Upload'); ?></a>
					<ul class="clearfix" id="fsUpload">
					<?php
					$meta = new MetaLibrary();
					$meta->setType(3);
					$meta->setPID( $pid );
					$attachments = $meta->getMeta();
					foreach( $attachments as $c ) :
					?>
						<li class="multiline" id="attach-<?php echo $c['mid']; ?>"><label for="attach-<?php echo $c['mid']; ?>"><?php echo $c['name']; ?></label><a href="#" onclick="insertToEditor('<?php path( array( 'mid'=>$c['mid'] ), 'Attachment' ); ?>','<?php echo $c['description']; ?>','<?php echo $c['name']; ?>');return false;">[<?php _e('Insert'); ?>]</a>&nbsp;&nbsp;<a href="#" onclick="deleteAttachment(<?php echo $c['mid']; ?>);return false;">[<?php _e('Delete'); ?>]</a></li>
					<?php
					endforeach;
					$meta->setPID( 1000000000 );
					$attachments = $meta->getMeta();
					foreach( $attachments as $c ) :
					?>
						<li class="multiline"><label for="attach-<?php echo $c['mid']; ?>"><?php echo $c['name']; ?></label><a href="#" onclick="insertToEditor('<?php path( array( 'mid'=>$c['mid'] ), 'Attachment' ); ?>','<?php echo $c['description']; ?>','<?php echo $c['name']; ?>');return false;">[<?php _e('Insert'); ?>]</a>&nbsp;&nbsp;<a href="#" onclick="deleteAttachment(<?php echo $c['mid']; ?>);return false;">[<?php _e('Delete'); ?>]</a></li>
					<?php
					endforeach;
					?>
					</ul>
				</li>
				<li>
					<input type="hidden" name="pid" value="<?php echo $pid; ?>" />
					<input type="submit" id="add-post-submit" value="<?php _e('Edit Post'); ?>" />
				</li>
				</ul>
			</div>
			<div id="add-post-left">
				<textarea autocomplete="off" id="add-post-content" name="content"><?php echo $p['content']; ?></textarea>
			</div>
		</form>
<?php
else:
?>
		<form action="<?php path(array('do'=>'addPost'),'AdminDo'); ?>" method="post" name="add_post" id="add-post">
			<div id="add-post-right">
				<ul id="add-post-option">
				<li>
					<label for="add-post-title" class="add-post-label"><?php _e('Title'); ?></label>
					<p><input type="text" id="add-post-title" name="title" value="" class="add-post-text" /></p>
				</li>
				<li>
					<label class="add-post-label"><?php _e('Categories'); ?></label>
					<ul class="clearfix">
					<?php
					$meta = new MetaLibrary();
					$meta->setType(1);
					$categories = $meta->getMeta( FALSE );

					foreach( $categories as $c ) {
						if( !Widget::getWidget('User')->checkPrivilege( 'POST', $c['mid'] ) ) {
							continue;
						}
					?>
						<li><input type="checkbox" id="category-<?php echo $c['mid']; ?>" value="<?php echo $c['mid']; ?>" name="category[]">
						<label for="category-<?php echo $c['mid']; ?>"><?php echo $c['name']; ?></label></li>
					<?php
					}
					?>
					</ul>
				</li>
				<li>
					<label class="add-post-label"><?php _e('Privilege'); ?></label>
					<ul class="clearfix">
						<li><input id="add-post-allowComment" name="allowComment" type="checkbox" value="1" checked="true">
						<label for="add-post-allowComment"><?php _e('Allow Comment'); ?></label></li>
						<li><input id="add-post-top" name="top" type="checkbox" value="1">
						<label for="add-post-top"><?php _e('Top Post'); ?></label></li>
					</ul>
				</li>
				<li>
					<label for="add-post-tags" class="add-post-label"><?php _e('Tags'); ?></label>
					<p><input id="add-post-tags" name="tags" type="text" value="" class="add-post-text" autocomplete="off"></p>
				</li>
				<li>
					<label class="add-post-label"><?php _e('Attachment'); ?></label> <a href="#" onclick="uploadPanel(); return false;" style="font-size:12px;"><?php _e('Upload'); ?></a>
					<ul class="clearfix" id="fsUpload">
					<?php
					$meta = new MetaLibrary();
					$meta->setType(3);
					$meta->setPID( 1000000000 );
					$attachments = $meta->getMeta();
					foreach( $attachments as $c ) :
					?>
						<li class="multiline" id="attach-<?php echo $c['mid']; ?>"><label for="attach-<?php echo $c['mid']; ?>"><?php echo $c['name']; ?></label><a href="#" onclick="insertToEditor('<?php path( array( 'mid'=>$c['mid'] ), 'Attachment' ); ?>','<?php echo $c['description']; ?>','<?php echo $c['name']; ?>');return false;">[<?php _e('Insert'); ?>]</a>&nbsp;&nbsp;<a href="#" onclick="deleteAttachment(<?php echo $c['mid']; ?>);return false;">[<?php _e('Delete'); ?>]</a></li>
					<?php
					endforeach;
					?>
					</ul>
				</li>
				<li>
					<input type="submit" id="add-post-submit" value="<?php _e('Add Post'); ?>" />
				</li>
				</ul>
			</div>
			<div id="add-post-left">
				<textarea autocomplete="off" id="add-post-content" name="content"></textarea>
			</div>
		</form>
<?php
endif;
?>
	</dd>
	<dd id="tabPostList" style="display: <?php if(Request::G('page')): ?>block<?php else: ?>none<?php endif; ?>; ">
		<p class="operate"><?php _e('Operation'); ?>: 
			<a href="#" onclick="checkAll('list-post'); return false;"><?php _e('Check All'); ?></a> , 
			<a href="#" onclick="uncheckAll('list-post'); return false;"><?php _e('Check None'); ?></a>&nbsp;&nbsp;&nbsp;
			<?php _e('Checked Item'); ?>: <a href="#" onclick="if(confirm('<?php _e('Sure to delete?'); ?>')){postDelete();}return false;"><?php _e('Delete'); ?></a>
		</p>
		<table class="list-table" id="list-post">
			<colgroup>
				<col width="25">
				<col width="200">
				<col width="100">
				<col width="155">
				<col width="130">
				<col width="150">
			</colgroup>
			<thead>
				<tr>
					<th class="radius-topleft"> </th>
					<th><?php _e('Title'); ?></th>
					<th><?php _e('Author'); ?></th>
					<th><?php _e('Category'); ?></th>
					<th><?php _e('Reply'); ?></th>
					<th class="radius-topright"><?php _e('Date'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$i = 0;
			Widget::initWidget('Post');
			Widget::getWidget('Post')->setPerPage( 10 );
			Widget::getWidget('Post')->setCurrentPage( Request::G('page') ? Request::G('page') : 1 );
			Widget::getWidget('Post')->query();
			while( post_next() ):
			?>
				<tr<?php if($i%2==0): ?> class="even"<?php endif; ?> id="post-<?php post_id(); ?>">
					<td><input type="checkbox" value="<?php post_id(); ?>" name="pid[]"></td>
					<td><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManagePosts'),'AdminDo'); ?>?pid=<?php post_id(); ?>');return false;"><?php post_title(); ?></a></td>
					<td><?php post_author(); ?></td>
					<td><?php post_category(); ?></td>
					<td><?php post_comment(); ?></td>
					<td><?php post_date(); ?></td>
				</tr>
			<?php
				$i ++;
			endwhile;
			?>
			</tbody>
		</table>
		<?php
		$nav = post_nav(FALSE);
		if( $nav ) {
			if( $nav['totalPage'] <= 20 ) {
		?>
		<div class="nav">
			<div class="nav-content">
				<ul>
				<?php for( $i=1 ; $i<=$nav['totalPage'] ; $i ++ ): ?>
					<li<?php if($i==$nav['currentPage']):?> class="current"<?php endif; ?>><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManagePosts'),'AdminDo'); ?>?page=<?php echo $i; ?>');return false;""><?php echo $i; ?></a></li>
				<?php endfor; ?>
				</ul>
			</div>
		</div>
		<?php
			} else {
				if( $nav['currentPage'] <= 9 ) {
					$start = 1;
					$end = 18;
				} else if( $nav['totalPage'] - $nav['currentPage'] <= 9 ) {
					$start = $nav['totalPage'] - 17;
					$end = $nav['totalPage'];
				} else {
					$start = $nav['currentPage'] - 8;
					$end = $nav['currentPage'] + 8;	
				}
		?>
		<div class="nav">
			<div class="nav-content">
				<ul>
				<?php if( $start != 1 ) { ?>
					<li<?php if($i==1):?> class="current"<?php endif; ?>><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManagePosts'),'AdminDo'); ?>?page=1');return false;""><?php _e('First'); ?></a></li>
				<?php } ?>
				<?php for( $i=$start ; $i<=$end ; $i ++ ): ?>
					<li<?php if($i==$nav['currentPage']):?> class="current"<?php endif; ?>><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManagePosts'),'AdminDo'); ?>?page=<?php echo $i; ?>');return false;""><?php echo $i; ?></a></li>
				<?php endfor; ?>
				<?php if( $end != $nav['totalPage'] ) { ?>
					<li<?php if($i==$nav['totalPage']):?> class="current"<?php endif; ?>><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManagePosts'),'AdminDo'); ?>?page=<?php echo $nav['totalPage']; ?>');return false;""><?php _e('Last'); ?></a></li>
				<?php } ?>
				</ul>
			</div>
		</div>
		<?php
			}
		}
		?>
	</dd>
</dl>
<script type="text/javascript" src="<?php path('User/Theme/admin/script/swfupload.js','base'); ?>"></script>
<script type="text/javascript" src="<?php path('User/Theme/admin/script/swfupload.queue.js','base'); ?>"></script>
<script type="text/javascript" src="<?php path('User/Theme/admin/script/fileprogress.js','base'); ?>"></script>
<script type="text/javascript" src="<?php path('User/Theme/admin/script/handlers.js','base'); ?>"></script>
<script>
Tabs(document.getElementById('PostsTabs'), 'select', <?php if(!Request::G('page')): ?>0<?php else: ?>1<?php endif; ?>);

$('#add-post').ajaxForm({
beforeSubmit: function(){
	$("#add-post-submit").attr('disabled',true);
},
success: function(data){
	data = eval('('+data+')');
	if( data.success ) {
		showMessage(data.message,'tips');
		ajaxLoad("<?php path(array('do'=>'contentManagePosts'),'AdminDo'); ?>?page=1");
	} else {
		showMessage(data.message,'error');
		$("#add-post-submit").attr('disabled',false);
	}
}});

function postDelete() {
	$("#list-post input:checked").each(function(){
		$.post(
			'<?php path(array("do"=>"delPost"),"AdminDo"); ?>',
			{'pid':$(this).val()},
			function(response){
				if(!response.success){
					showMessage(response.message,'error');
				}
			},
			"json"
		);
	});
	ajaxLoad('<?php path(array("do"=>"contentManagePosts"),"AdminDo"); ?>?page=<?php echo Request::G("page")?Request::G("page"):1; ?>');
}

var settings = {
	flash_url : "<?php path('User/Theme/admin/script/swfupload.swf','base'); ?>",
	upload_url: "<?php path(array('do'=>'upload'),'AdminDo'); ?>",
	post_params: {"userid" : "<?php echo Request::C('userid'); ?>", "password" : "<?php echo Request::C('password','string'); ?>"},
	file_size_limit : "2 MB",
	file_types : "*.jpg;*.png;*.jpeg;*.gif;*.bmp;*.rar;*.zip;*.tar.gz",
	file_types_description : "<?php _e('Common Files'); ?>",
	file_upload_limit : 100,
	file_queue_limit : 0,
	custom_settings : {
		progressTarget : "fsUploadProgress",
		cancelButtonId : "btnCancel"
	},
	debug: false,

	// Button settings
	button_image_url: "<?php path('User/Theme/admin/image/upload.png','base'); ?>",
	button_width: "65",
	button_height: "29",
	button_placeholder_id: "spanButtonPlaceHolder",
	button_text: '<span class="theFont"><?php _e("Upload"); ?></span>',
	button_text_style: ".theFont { font-size: 16; }",
	button_text_left_padding: 10,
	button_text_top_padding: 3,
	
	// The event handler functions are defined in handlers.js
	file_queued_handler : fileQueued,
	file_queue_error_handler : fileQueueError,
	file_dialog_complete_handler : fileDialogComplete,
	upload_start_handler : uploadStart,
	upload_progress_handler : uploadProgress,
	upload_error_handler : uploadError,
	upload_success_handler : uploadSuccess,
	upload_complete_handler : uploadComplete,
	queue_complete_handler : queueComplete	// Queue plugin event
};
var swfu;

function uploadPanel() {
	art.dialog({
		content:'<div class="fieldset flash" id="fsUploadProgress"></div><div id="divStatus">0 个文件已上传</div><div style="padding-top:10px;padding-left:5px;"><span id="spanButtonPlaceHolder"></span><input id="btnCancel" type="button" value="取消上传" onclick="swfu.cancelQueue();" disabled="disabled" style="margin-left: 2px; font-size: 8pt; height: 29px;" /></div>',
		fixed:true,
		title:'<?php _e("Upload"); ?>',
		width:400,
		id:'uploadPanel',
		style:'noIcon'
	});
	swfu = new SWFUpload(settings);
}

function deleteAttachment( mid ) {
	$.post(
		'<?php path(array("do"=>"delMeta"),"AdminDo"); ?>',
		{'mid':mid},
		function(response){
			if(!response.success){
				showMessage(response.message,'error');
			} else {
				$('#attach-'+mid).remove();
			}
		},
		"json"
	);
}
</script>
<?php if( $editor = Plugin::call('editor','') ) { echo $editor; } else { ?>
<script>
function insertToEditor( url, mime, name ) {
	if( mime.replace('image','') != mime ) {
		$('#add-post-content').insertAtCaret( '<img src="'+url+'" />' );
	} else {
		$('#add-post-content').insertAtCaret( '<a href="'+url+'" target="_blank">'+name+'</a>' );
	}
}
</script>
<?php } ?>
