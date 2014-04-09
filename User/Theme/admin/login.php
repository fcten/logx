<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="imagetoolbar" content="no" />
	<title><?php _e("Login"); ?> &lsaquo; <?php title(); ?></title>
	<link rel="stylesheet" href="<?php path('User/Theme/admin/css/style.css','base'); ?>" />
	<script type="text/javascript" src="<?php path('User/Theme/admin/script/jquery.js','base'); ?>"></script>
	<script type="text/javascript" src="<?php path('User/Theme/admin/script/jquery.form.js','base'); ?>"></script>
	<script type="text/javascript" src="<?php path('User/Theme/admin/script/tab.js','base'); ?>"></script>
	<script>
	$(document).ready(function() {
		Tabs(document.getElementById('AdminTabs'), 'select', 0);
		$('#login').ajaxForm({
		beforeSubmit: function(){
			$("#l_submit").attr('disabled',true);
		},
		success: function(data){
			data = eval('('+data+')');
			if( data.success ) {
				$("#login-tips").html('<font color="blue">'+data.message+'</font>');
				setTimeout( "document.location.href='./';", 3000);
			} else {
				$("#login-tips").html('<font color="red">'+data.message+'</font>');
				$("#l_submit").attr('disabled',false);
			}
		}});
		$('#register').ajaxForm({
		beforeSubmit: function(){
			$("#r_submit").attr('disabled',true);
		},
		success: function(data){
			data = eval('('+data+')');
			if( data.success ) {
				$("#tabRegister").html('<div class="success">'+data.message+'</div>');
			} else {
				$("#login-tips").html('<font color="red">'+data.message+'</font>');
				$("#r_submit").attr('disabled',false);
			}
		}});
	});
	</script>
</head>
<body style="background-color: #F6F8FF;">
<?php
$register = ( OptionLibrary::get('register') == 'close' ) ? FALSE : TRUE;
?>
	<div id="content">
		<div id="content-in">
			<dl class="Tabs login-box">
				<dt id="AdminTabs"><a href="#tabLogin" class="select"><?php _e('Login'); ?></a> <?php if($register){ ?><a href="#tabRegister"><?php _e('Register'); ?></a><?php } ?><span id="login-tips"><?php printf( _t("Powered by LogX V%s"), LOGX_VERSION ); ?></span></dt>
				<dd id="tabLogin" style="display: block; ">
					<form name="login" id="login" action="<?php path(array('do'=>'login'),'Action'); ?>" method="post"> 
						<p> 
							<label><?php _e("Username"); ?><br /> 
							<input type="text" name="username" id="username" class="input" value="" size="20" tabindex="10" /></label> 
						</p> 
						<p> 
							<label><?php _e("Password"); ?><br /> 
							<input type="password" name="password" id="password" class="input" value="" size="20" tabindex="20" /></label> 
						</p> 
						<p class="forgetmenot">
							<label><input name="remember" type="checkbox" id="remember" value="2592000" tabindex="90" /> <?php _e("Remember me"); ?></label>
						</p> 
						<p class="submit"> 
							<input type="submit" name="l_submit" id="l_submit" value="<?php _e('Login'); ?>" tabindex="100" /> 
						</p>
					</form> 
				</dd>
				<?php if($register){ ?>
				<dd id="tabRegister" style="display: none; ">
					<form name="register" id="register" action="<?php path(array('do'=>'register'),'Action'); ?>" method="post"> 
						<p> 
							<label><?php _e("Username"); ?><br /> 
							<input type="text" name="username" id="username" class="input" value="" size="20" tabindex="10" /></label> 
						</p> 
						<p> 
							<label><?php _e("Email"); ?><br /> 
							<input type="text" name="email" id="email" class="input" value="" size="20" tabindex="20" /></label> 
						</p> 
						<p class="submit"> 
							<input type="submit" name="r_submit" id="r_submit" value="<?php _e('Register'); ?>" tabindex="100" /> 
						</p>
					</form> 
				</dd>
				<?php } ?>
			</dl>
		</div>
		<div class="clear"></div>
	</div>

	<!--[if lt IE 7]>
	<script>
		$("#login-tips").html('<font color="red"><?php _e('Unsupported browser.'); ?></font>');
		$("#submit").attr('disabled',true);
	</script>
	<![endif]-->

</body>
</html>
