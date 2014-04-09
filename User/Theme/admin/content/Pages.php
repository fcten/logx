<dl class="Tabs">
	<dt id="PagesTabs"><a href="#tabPageAdd"<?php if(!Request::G('page')): ?> class="select"<?php endif; ?>><?php if(!Request::G('pid')){ _e('Add'); }else{ _e('Edit'); } ?></a> <a href="#tabPageList"<?php if(Request::G('page')): ?> class="select"<?php endif; ?>><?php _e('List'); ?></a></dt>
	<dd id="tabPageAdd" style="display: <?php if(!Request::G('page')): ?>block<?php else: ?>none<?php endif; ?>; ">
<?php
if( $pid = Request::G('pid') ):
	$post = new PostLibrary();
	$p = $post->getPage( $pid, FALSE );
?>
		<form action="<?php path(array('do'=>'editPage'),'AdminDo'); ?>" method="post" name="add_post" id="add-post">
			<div id="add-post-left">
				<textarea style="height: 440px;width:550px;" autocomplete="off" id="add-post-content" name="content"><?php echo $p['content']; ?></textarea>
			</div>
			<div id="add-post-right">
				<ul id="add-post-option">
				<li>
					<label for="add-post-title" class="add-post-label"><?php _e('Title'); ?></label>
					<p><input type="text" id="add-post-title" name="title" value="<?php echo $p['title']; ?>" class="add-post-text" /></p>
				</li>
				<li>
					<label for="add-post-alias" class="add-post--label"><?php _e('Alias'); ?></label>
					<p><input type="text" id="add-post-alias" name="alias" value="<?php echo $p['alias']; ?>" class="add-post-text" ></p>
				</li>
				<li>
					<label class="add-post-label"><?php _e('Privilege'); ?></label>
					<ul class="clearfix">
						<li><input id="add-post-allowComment" name="allowComment" type="checkbox" value="1"<?php if( $p['allow_reply'] ): ?> checked="true"<?php endif; ?>>
						<label for="add-post-allowComment"><?php _e('Allow Comment'); ?></label></li>
					</ul>
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
						<li class="multiline"><label for="attach-<?php echo $c['mid']; ?>"><?php echo $c['name']; ?></label><a href="#" onclick="insertToEditor('<?php path( array( 'mid'=>$c['mid'] ), 'Attachment' ); ?>','<?php echo $c['description']; ?>','<?php echo $c['name']; ?>');return false;">[<?php _e('Insert'); ?>]</a>&nbsp;&nbsp;<a href="#" onclick="deleteAttachment(<?php echo $c['mid']; ?>);return false;">[<?php _e('Delete'); ?>]</a></li>
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
					<input type="submit" id="add-post-submit" value="<?php _e('Edit Page'); ?>" />
				</li>
				</ul>
			</div>
		</form>
<?php
else:
?>
		<form action="<?php path(array('do'=>'addPage'),'AdminDo'); ?>" method="post" name="add_post" id="add-post">
			<div id="add-post-left">
				<textarea style="height: 440px;width:550px;" autocomplete="off" id="add-post-content" name="content"></textarea>
			</div>
			<div id="add-post-right">
				<ul id="add-post-option">
				<li>
					<label for="add-post-title" class="add-post-label"><?php _e('Title'); ?></label>
					<p><input type="text" id="add-post-title" name="title" value="" class="add-post-text" /></p>
				</li>
				<li>
					<label for="add-post-alias" class="add-post--label"><?php _e('Alias'); ?></label>
					<p><input type="text" id="add-post-alias" name="alias" value="" class="add-post-text" ></p>
				</li>
				<li>
					<label class="add-post-label"><?php _e('Privilege'); ?></label>
					<ul class="clearfix">
						<li><input id="add-post-allowComment" name="allowComment" type="checkbox" value="1" checked="true">
						<label for="add-post-allowComment"><?php _e('Allow Comment'); ?></label></li>
					</ul>
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
						<li class="multiline"><label for="attach-<?php echo $c['mid']; ?>"><?php echo $c['name']; ?></label><a href="#" onclick="insertToEditor('<?php path( array( 'mid'=>$c['mid'] ), 'Attachment' ); ?>','<?php echo $c['description']; ?>','<?php echo $c['name']; ?>');return false;">[<?php _e('Insert'); ?>]</a>&nbsp;&nbsp;<a href="#" onclick="deleteAttachment(<?php echo $c['mid']; ?>);return false;">[<?php _e('Delete'); ?>]</a></li>
					<?php
					endforeach;
					?>
					</ul>
				</li>
				<li>
					<input type="submit" id="add-post-submit" value="<?php _e('Add Page'); ?>" />
				</li>
				</ul>
			</div>
		</form>
<?php
endif;
?>
	</dd>
	<dd id="tabPageList" style="display: <?php if(Request::G('page')): ?>block<?php else: ?>none<?php endif; ?>; ">
		<p class="operate"><?php _e('Operation'); ?>: 
			<a href="#" onclick="checkAll('list-page'); return false;"><?php _e('Check All'); ?></a> , 
			<a href="#" onclick="uncheckAll('list-page'); return false;"><?php _e('Check None'); ?></a>&nbsp;&nbsp;&nbsp;
			<?php _e('Checked Item'); ?>: <a href="#" onclick="if(confirm('<?php _e('Sure to delete?'); ?>')){pageDelete();}return false;"><?php _e('Delete'); ?></a>
		</p>
		<table class="list-table" id="list-page">
			<colgroup>
				<col width="25">
				<col width="355">
				<col width="380">
			</colgroup>
			<thead>
				<tr>
					<th class="radius-topleft"> </th>
					<th><?php _e('Title'); ?></th>
					<th class="radius-topright"><?php _e('Alias'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$i = 0;
			Widget::initWidget('Page');
			while( page_next() ):
				if( page_id(FALSE) ):
			?>
				<tr<?php if($i%2==0): ?> class="even"<?php endif; ?> id="page-<?php page_id(); ?>">
					<td><input type="checkbox" value="<?php page_id(); ?>" name="pid[]"></td>
					<td><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManagePages'),'AdminDo'); ?>?pid=<?php page_id(); ?>');return false;"><?php echo page_title(); ?></a></td>
					<td><?php page_alias(); ?></td>
				</tr>
			<?php
					$i ++;
				endif;
			endwhile;
			?>
			</tbody>
		</table>
		<?php  ?>
	</dd>
</dl>
<script type="text/javascript" src="<?php path('User/Theme/admin/script/swfupload.js','base'); ?>"></script>
<script type="text/javascript" src="<?php path('User/Theme/admin/script/swfupload.queue.js','base'); ?>"></script>
<script type="text/javascript" src="<?php path('User/Theme/admin/script/fileprogress.js','base'); ?>"></script>
<script type="text/javascript" src="<?php path('User/Theme/admin/script/handlers.js','base'); ?>"></script>
<script>
Tabs(document.getElementById('PagesTabs'), 'select', <?php if(!Request::G('page')): ?>0<?php else: ?>1<?php endif; ?>);
$('#add-post').ajaxForm({
beforeSubmit: function(){
	$("#add-post-submit").attr('disabled',true);
},
success: function(data){
	data = eval('('+data+')');
	if( data.success ) {
		showMessage(data.message,'tips');
		setTimeout( "$('#h_Page').click();", 3000);
	} else {
		showMessage(data.message,'error');
		$("#add-post-submit").attr('disabled',false);
	}
}});
function pageDelete() {
	$("#list-page input:checked").each(function(){
		$.post(
			'<?php path(array("do"=>"delPage"),"AdminDo"); ?>',
			{'pid':$(this).val()},
			function(response){
				if(!response.success){
					showMessage(response.message,'error');
				}
			},
			"json"
		);
	});
	ajaxLoad('<?php path(array("do"=>"contentManagePages"),"AdminDo"); ?>?page=<?php echo Request::G("page")?Request::G("page"):1; ?>');
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
