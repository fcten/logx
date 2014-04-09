<dl class="Tabs">
	<dt id="CommentsTabs">
		<a href="#tabCommentList" <?php if(!isset($_GET['cpage'])):?>class="select"<?php endif;?>><?php _e('List'); ?></a>
		<a href="#tabCommentCensor" <?php if(isset($_GET['cpage'])):?>class="select"<?php endif;?>><?php _e('Censor'); ?></a>
	</dt>
	<dd id="tabCommentList" <?php if(!isset($_GET['cpage'])):?>style="display: block; "<?php else: ?>style="display: none; "<?php endif;?>>
		<p class="operate"><?php _e('Operation'); ?>: 
			<a href="#" onclick="checkAll('list-comment'); return false;"><?php _e('Check All'); ?></a> , 
			<a href="#" onclick="uncheckAll('list-comment'); return false;"><?php _e('Check None'); ?></a>&nbsp;&nbsp;&nbsp;
			<?php _e('Checked Item'); ?>: <a href="#" onclick="if(confirm('<?php _e('Sure to delete?'); ?>')){commentDelete();}return false;"><?php _e('Delete'); ?></a>
		</p>
		<table class="list-table" id="list-comment">
			<colgroup>
				<col width="25">
				<col width="200">
				<col width="385">
				<col width="150">
			</colgroup>
			<thead>
				<tr>
					<th class="radius-topleft"> </th>
					<th><?php _e('Author'); ?></th>
					<th><?php _e('Content'); ?></th>
					<th class="radius-topright"><?php _e('Date'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$i = 0;
			Widget::initWidget('Comment');
			Widget::getWidget('Comment')->setPerPage( 20 );
			Widget::getWidget('Comment')->setCurrentPage( Request::G('page') ? Request::G('page') : 1 );
			Widget::getWidget('Comment')->query();
			while( comment_next() ):
			?>
				<tr<?php if($i%2==0): ?> class="even"<?php endif; ?> id="post-<?php comment_id(); ?>">
					<td><input type="checkbox" value="<?php comment_id(); ?>" name="cid[]"></td>
					<td><?php comment_author(); ?></td>
					<td><?php comment_content(); ?></td>
					<td><?php comment_date(); ?></td>
				</tr>
			<?php
				$i ++;
			endwhile;
			?>
			</tbody>
		</table>
		<p class="operate"><?php _e('Operation'); ?>: 
			<a href="#" onclick="checkAll('list-comment'); return false;"><?php _e('Check All'); ?></a> , 
			<a href="#" onclick="uncheckAll('list-comment'); return false;"><?php _e('Check None'); ?></a>&nbsp;&nbsp;&nbsp;
			<?php _e('Checked Item'); ?>: <a href="#" onclick="if(confirm('<?php _e('Sure to delete?'); ?>')){commentDelete();}return false;"><?php _e('Delete'); ?></a>
		</p>
		<?php
		$nav = comment_nav(FALSE);
		if( $nav ) {
			if( $nav['totalPage'] <= 20 ) {
		?>
		<div class="nav">
			<div class="nav-content">
				<ul>
				<?php for( $i=1 ; $i<=$nav['totalPage'] ; $i ++ ): ?>
					<li<?php if($i==$nav['currentPage']):?> class="current"<?php endif; ?>><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManageComments'),'AdminDo'); ?>?page=<?php echo $i; ?>');return false;""><?php echo $i; ?></a></li>
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
					<li><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManageComments'),'AdminDo'); ?>?page=1');return false;""><?php _e('First'); ?></a></li>
				<?php } ?>
				<?php for( $i=$start ; $i<=$end ; $i ++ ): ?>
					<li<?php if($i==$nav['currentPage']):?> class="current"<?php endif; ?>><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManageComments'),'AdminDo'); ?>?page=<?php echo $i; ?>');return false;""><?php echo $i; ?></a></li>
				<?php endfor; ?>
				<?php if( $end != $nav['totalPage'] ) { ?>
					<li><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManageComments'),'AdminDo'); ?>?page=<?php echo $nav['totalPage']; ?>');return false;""><?php _e('Last'); ?></a></li>
				<?php } ?>
				</ul>
			</div>
		</div>
		<?php
			}
		}
		?>
	</dd>
	<dd id="tabCommentCensor" <?php if(isset($_GET['cpage'])):?>style="display: block; "<?php else: ?>style="display: none; "<?php endif;?>>
		<p class="operate"><?php _e('Operation'); ?>: 
			<a href="#" onclick="checkAll('list-comment-censor'); return false;"><?php _e('Check All'); ?></a> , 
			<a href="#" onclick="uncheckAll('list-comment-censor'); return false;"><?php _e('Check None'); ?></a>&nbsp;&nbsp;&nbsp;
			<?php _e('Checked Item'); ?>: <a href="#" onclick="if(confirm('<?php _e('Sure to delete?'); ?>')){commentCensorDelete();}return false;"><?php _e('Delete'); ?></a> , 
			<a href="#" onclick="commentCensor();return false;"><?php _e('Censor'); ?></a>
		</p>
		<table class="list-table" id="list-comment-censor">
			<colgroup>
				<col width="25">
				<col width="200">
				<col width="385">
				<col width="150">
			</colgroup>
			<thead>
				<tr>
					<th class="radius-topleft"> </th>
					<th><?php _e('Author'); ?></th>
					<th><?php _e('Content'); ?></th>
					<th class="radius-topright"><?php _e('Date'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$i = 0;
			Widget::initWidget('Comment');
			Widget::getWidget('Comment')->setStatus( 2 );
			Widget::getWidget('Comment')->setPerPage( 20 );
			Widget::getWidget('Comment')->setCurrentPage( Request::G('cpage') ? Request::G('cpage') : 1 );
			Widget::getWidget('Comment')->query();
			while( comment_next() ):
			?>
				<tr<?php if($i%2==0): ?> class="even"<?php endif; ?> id="post-<?php comment_id(); ?>">
					<td><input type="checkbox" value="<?php comment_id(); ?>" name="cid[]"></td>
					<td><?php comment_author(); ?></td>
					<td><?php comment_content(); ?></td>
					<td><?php comment_date(); ?></td>
				</tr>
			<?php
				$i ++;
			endwhile;
			?>
			</tbody>
		</table>
		<p class="operate"><?php _e('Operation'); ?>: 
			<a href="#" onclick="checkAll('list-comment-censor'); return false;"><?php _e('Check All'); ?></a> , 
			<a href="#" onclick="uncheckAll('list-comment-censor'); return false;"><?php _e('Check None'); ?></a>&nbsp;&nbsp;&nbsp;
			<?php _e('Checked Item'); ?>: <a href="#" onclick="if(confirm('<?php _e('Sure to delete?'); ?>')){commentCensorDelete();}return false;"><?php _e('Delete'); ?></a> , 
			<a href="#" onclick="commentCensor();return false;"><?php _e('Censor'); ?></a>
		</p>
		<?php
		$nav = comment_nav(FALSE);
		if( $nav ) {
			if( $nav['totalPage'] <= 20 ) {
		?>
		<div class="nav">
			<div class="nav-content">
				<ul>
				<?php for( $i=1 ; $i<=$nav['totalPage'] ; $i ++ ): ?>
					<li<?php if($i==$nav['currentPage']):?> class="current"<?php endif; ?>><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManageComments'),'AdminDo'); ?>?page=<?php echo $i; ?>');return false;""><?php echo $i; ?></a></li>
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
					<li><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManageComments'),'AdminDo'); ?>?cpage=1');return false;""><?php _e('First'); ?></a></li>
				<?php } ?>
				<?php for( $i=$start ; $i<=$end ; $i ++ ): ?>
					<li<?php if($i==$nav['currentPage']):?> class="current"<?php endif; ?>><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManageComments'),'AdminDo'); ?>?cpage=<?php echo $i; ?>');return false;""><?php echo $i; ?></a></li>
				<?php endfor; ?>
				<?php if( $end != $nav['totalPage'] ) { ?>
					<li><a href="#" onclick="ajaxLoad('<?php path(array('do'=>'contentManageComments'),'AdminDo'); ?>?cpage=<?php echo $nav['totalPage']; ?>');return false;""><?php _e('Last'); ?></a></li>
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
<script>
Tabs(document.getElementById('CommentsTabs'), 'select', <?php if(!isset($_GET['cpage'])):?>0<?php else: ?>1<?php endif;?>);

function commentDelete() {
	$("#list-comment input:checked").each(function(){
		$.post(
			'<?php path(array("do"=>"delComment"),"AdminDo"); ?>',
			{'cid':$(this).val()},
			function(response){
				if(!response.success){
					showMessage(response.message,'error');
				}
			},
			"json"
		);
	});
	ajaxLoad('<?php path(array("do"=>"contentManageComments"),"AdminDo"); ?>?page=<?php echo Request::G("page")?Request::G("page"):1; ?>');
}

function commentCensorDelete() {
	$("#list-comment-censor input:checked").each(function(){
		$.post(
			'<?php path(array("do"=>"delComment"),"AdminDo"); ?>',
			{'cid':$(this).val()},
			function(response){
				if(!response.success){
					showMessage(response.message,'error');
				}
			},
			"json"
		);
	});
	ajaxLoad('<?php path(array("do"=>"contentManageComments"),"AdminDo"); ?>?cpage=<?php echo Request::G("cpage")?Request::G("cpage"):1; ?>');
}

function commentCensor() {
	$("#list-comment-censor input:checked").each(function(){
		$.post(
			'<?php path(array("do"=>"censorComment"),"AdminDo"); ?>',
			{'cid':$(this).val()},
			function(response){
				if(!response.success){
					showMessage(response.message,'error');
				}
			},
			"json"
		);
	});
	ajaxLoad('<?php path(array("do"=>"contentManageComments"),"AdminDo"); ?>?cpage=<?php echo Request::G("cpage")?Request::G("cpage"):1; ?>');
}
</script>
