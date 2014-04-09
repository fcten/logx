<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="imagetoolbar" content="no" />
	<title><?php _e("Terminal"); ?> &lsaquo; <?php name(); ?></title>
	<link rel="stylesheet" href="<?php path('User/Theme/admin/css/style.css','base'); ?>" />
	<script type="text/javascript" src="<?php path('User/Theme/admin/script/jquery.js','base'); ?>"></script>
	<script type="text/javascript" src="<?php path('User/Theme/admin/script/jquery.form.js','base'); ?>"></script>
	<script type="text/javascript" src="<?php path('User/Theme/admin/script/jquery.insert.js','base'); ?>"></script>
	<script type="text/javascript" src="<?php path('User/Theme/admin/script/jquery.ba-dotimeout.min.js','base'); ?>"></script>
	<script type="text/javascript" src="<?php path('User/Theme/admin/script/tab.js','base'); ?>"></script>
	<script type="text/javascript" src="<?php path('User/Theme/admin/script/artDialog.js','base'); ?>"></script>
	<script>
	var PATH = '<?php path("","base"); ?>';
	var NAME = '<?php name(); ?>';
	$(document).ready(function() {
		initMenu();

		if( ('onhashchange' in window) && ((typeof document.documentMode==='undefined') || document.documentMode==8)) {
			window.onhashchange = pageLoad;
		}
	});
	</script>
</head>
<body>
	<div id="header">
		<div id="menu">
			<div id="logo">
				<img src="<?php path('User/Theme/admin/image/logo.gif','base'); ?>" />
				<span id="tips">简单轻巧、优雅随心</span>
			</div>
			<dl class="artTabs">
				<ul id="tabs">
					<li><a class="tabTop" id="h_Terminal" href="<?php path(array('do'=>'terminal'),'AdminDo'); ?>"><?php _e("Terminal"); ?></a></li>
					<li>
						<a class="tabTop" id="h_Content" href="#"><?php _e("Content"); ?></a>
						<div>
							<a class="tabChild" id="h_Post" href="<?php path(array('do'=>'contentManagePosts'),'AdminDo'); ?>"><?php _e("Posts"); ?></a>
							<a class="tabChild" id="h_Page" href="<?php path(array('do'=>'contentManagePages'),'AdminDo'); ?>"><?php _e("Pages"); ?></a>
							<a class="tabChild" id="h_Comment" href="<?php path(array('do'=>'contentManageComments'),'AdminDo'); ?>"><?php _e("Comments"); ?></a>
							<a class="tabChild" id="h_Category" href="<?php path(array('do'=>'contentManageCategories'),'AdminDo'); ?>"><?php _e("Categories"); ?></a>
							<a class="tabChild" id="h_Tag" href="<?php path(array('do'=>'contentManageTags'),'AdminDo'); ?>"><?php _e("Tags"); ?></a>
						</div>
					</li>
					<li><a class="tabTop" id="h_Theme" href="<?php path(array('do'=>'theme'),'AdminDo'); ?>"><?php _e("Theme"); ?></a></li>
					<li><a class="tabTop" id="h_Plugin" href="<?php path(array('do'=>'plugin'),'AdminDo'); ?>"><?php _e("Plugin"); ?></a></li>
					<li><a class="tabTop" id="h_User" href="<?php path(array('do'=>'user'),'AdminDo'); ?>"><?php _e("User"); ?></a></li>
					<li><a class="tabTop" id="h_System" href="<?php path(array('do'=>'system'),'AdminDo'); ?>"><?php _e("System"); ?></a></li>
				</ul>
			</dl>
		</div>
		<div class="clear"></div>
	</div>

	<div id="content">
		<div id="content-in">
