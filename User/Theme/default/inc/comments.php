<script>
// @ 回复
function comment_reply( cid ) {
	document.getElementById('comment_content').value = "@comment-" + cid + "\n" + document.getElementById('comment_content').value;
	setTimeout( 'document.getElementById("comment_content").focus();', 50 );
}
</script>
<div id="comments">
	<?php echo plugin_call('preComment'); ?>

	<h4><?php post_comment(_t('No Comments'), _t('Only One Comment &raquo;'), _t('%d Comments &raquo;')); ?></h4>

	<ol class="comment-list">
	<?php while(comment_next()): ?>
		<li id="comment-<?php comment_id(); ?>" class="comment-body"> 
			<div class="comment-author"> 
				<img class="avatar" src="<?php comment_avatar(); ?>" alt="<?php comment_author(); ?>" width="32" height="32" />
				<cite class="fn"><a href="<?php comment_website(); ?>" rel="external nofollow"><?php comment_author(); ?></a></cite> 
			</div> 
			<div class="comment-meta"> 
				<a href="<?php post_link(); ?>#comment-<?php comment_id(); ?>"><?php comment_date('F jS, Y, g:i:s a'); ?></a> 
			</div> 
			<p><?php comment_content(); ?></p>
			<div class="comment-reply"> 
				<a href="#" rel="nofollow" onclick="comment_reply(<?php comment_id(); ?>);return false;">回复</a>
			</div> 
		</li> 
	<?php endwhile; ?> 
	</ol>

	<?php echo plugin_call('afterComment'); ?>

 <?php if(post_allow_reply()): ?>
	<div class="respond">
            
		<h4 id="response"><?php _e('Leave a reply'); ?> &raquo;</h4>
		<form method="post" action="<?php path(array('do'=>'comment'),'Action'); ?>" id="comment_form">
			<input type="hidden" name="postId" id="postId" value="<?php post_id(); ?>" />
		<?php if( user_is_login() ): ?>
			<p>Logged in as <?php user_name(); ?>. <a href="<?php path(array('do'=>'logout'),'Action'); ?>" title="Logout"><?php _e('Logout'); ?> &raquo;</a></p>
		<?php else: ?>
			<p>
				<label for="author"><?php _e('Author'); ?><span class="required">*</span></label>
				<input type="text" name="author" id="author" class="text" size="15" value="<?php echo Request::C('author'); ?>" />
			</p>
			<p>
				<label for="mail"><?php _e('Email'); ?><span class="required">*</span></label>
				<input type="text" name="email" id="email" class="text" size="15" value="<?php echo Request::C('email'); ?>" />
			</p>
			<p>
				<label for="url"><?php _e('Website'); ?></label>
				<input type="text" name="website" id="website" class="text" size="15" value="<?php echo Request::C('website'); ?>" />
			</p>
		<?php endif; ?>
			<p><textarea rows="5" cols="50" name="content" class="textarea" id="comment_content"></textarea></p>
			<p><input type="submit" value="<?php _e('Post Comment'); ?>" class="submit" /></p>
		</form>
	</div>
<?php else: ?>
	<h4><?php _e('Comment Closed'); ?></h4>
<?php endif; ?>
</div>
