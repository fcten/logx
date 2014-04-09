<div class="box box-middle">
	<div class="box-title">
		<h3><?php _e('Basic Settings'); ?></h3>
		<span></span>
	</div>
	<div class="box-content">
		<form action="<?php path(array('do'=>'basicSettings'),'AdminDo'); ?>" method="post" name="basic_settings" id="basic-settings">
			<ul id="add-post-option">
			<li>
				<label for="basic-settings-name" class="add-post-label"><?php _e('Website Name'); ?></label>
				<p><input type="text" id="basic-settings-name" name="name" value="<?php echo OptionLibrary::get('title'); ?>" class="add-post-text" /></p>
			</li>
			<li>
				<label for="basic-settings-keywords" class="add-post-label"><?php _e('Website Keywords'); ?></label>
				<p><input id="basic-settings-keywords" name="keywords" type="text" value="<?php echo OptionLibrary::get('keywords'); ?>" class="add-post-text" autocomplete="off"></p>
			</li>
			<li>
				<label for="basic-settings-description" class="add-post-label"><?php _e('Website Description'); ?></label>
				<p><input id="basic-settings-description" name="description" type="text" value="<?php echo OptionLibrary::get('description'); ?>" class="add-post-text" autocomplete="off"></p>
			</li>
			<li>
				<label for="basic-settings-domain" class="add-post-label"><?php _e('Website Domain'); ?></label>
				<p><input id="basic-settings-domain" name="domain" type="text" value="<?php echo OptionLibrary::get('domain'); ?>" class="add-post-text" autocomplete="off"></p>
			</li>
			<li>
				<input type="submit" id="basic-settings-submit" value="<?php _e('Save Settings'); ?>" />
			</li>
			</ul>
		</form>
	</div>
</div>

<div class="box box-middle">
	<div class="box-title">
		<h3><?php _e('Advanced Settings'); ?></h3>
		<span></span>
	</div>
	<div class="box-content">
		<form action="<?php path(array('do'=>'advancedSettings'),'AdminDo'); ?>" method="post" name="advanced_settings" id="advanced-settings">
			<ul id="add-post-option">
			<li>
				<label class="add-post-label"><?php _e('User Register') ?></label>
				<?php $register = OptionLibrary::get('register'); ?>
				<ul class="clearfix">
				<li>
					<input name="register" type="radio" value="close" id="register-0"<?php if($register=='close'): ?> checked="true"<?php endif; ?>>
					<label for="register-0"><?php _e('Close'); ?></label>
				</li>
				<li>
					<input name="register" type="radio" value="open" id="register-1"<?php if($register=='open'): ?> checked="true"<?php endif; ?>>
					<label for="register-1"><?php _e('Open'); ?></label>
				</li>
				</ul>
				<p class="description"><?php _e('By default new users are not allowed to post.'); ?></p>
			</li>
			<li>
				<label class="add-post-label"><?php _e('URL Rewrite'); ?></label>
				<?php $rewrite = OptionLibrary::get('rewrite'); ?>
				<ul class="clearfix">
				<li>
					<input name="rewrite" type="radio" value="close" id="rewrite-0"<?php if($rewrite=='close'): ?> checked="true"<?php endif; ?>>
					<label for="rewrite-0"><?php _e('Close'); ?></label>
				</li>
				<li>
					<input name="rewrite" type="radio" value="open" id="rewrite-1"<?php if($rewrite=='open'): ?> checked="true"<?php endif; ?>>
					<label for="rewrite-1"><?php _e('Open'); ?></label>
				</li>
				</ul>
				<p class="description"><?php _e('Please make sure that your server supports rewrite.'); ?></p>
			</li>
			<li>
				<label class="add-post-label" for="timezone"><?php _e('Timezone'); ?></label>
				<?php $timezone = OptionLibrary::get('timezone'); ?>
				<ul class="clearfix">
				<li>
					<select name="timezone" id="timezone">
					<option value="Etc/GMT"<?php if($timezone=='Etc/GMT'): ?> selected="true"<?php endif; ?>>格林威治(子午线)标准时间 (GMT)</option>
					<option value="Etc/GMT-1"<?php if($timezone=='Etc/GMT-1'): ?> selected="true"<?php endif; ?>>中欧标准时间 阿姆斯特丹,荷兰,法国 (GMT +1)</option>
					<option value="Etc/GMT-2"<?php if($timezone=='Etc/GMT-2'): ?> selected="true"<?php endif; ?>>东欧标准时间 布加勒斯特,塞浦路斯,希腊 (GMT +2)</option>
					<option value="Etc/GMT-3"<?php if($timezone=='Etc/GMT-3'): ?> selected="true"<?php endif; ?>>莫斯科时间 伊拉克,埃塞俄比亚,马达加斯加 (GMT +3)</option>
					<option value="Etc/GMT-4"<?php if($timezone=='Etc/GMT-4'): ?> selected="true"<?php endif; ?>>第比利斯时间 阿曼,毛里塔尼亚,留尼汪岛 (GMT +4)</option>
					<option value="Etc/GMT-5"<?php if($timezone=='Etc/GMT-5'): ?> selected="true"<?php endif; ?>>新德里时间 巴基斯坦,马尔代夫 (GMT +5)</option>
					<option value="Etc/GMT-6"<?php if($timezone=='Etc/GMT-6'): ?> selected="true"<?php endif; ?>>科伦坡时间 孟加拉 (GMT +6)</option>
					<option value="Etc/GMT-7"<?php if($timezone=='Etc/GMT-7'): ?> selected="true"<?php endif; ?>>曼谷雅加达 柬埔寨,苏门答腊,老挝 (GMT +7)</option>
					<option value="Etc/GMT-8"<?php if($timezone=='Etc/GMT-8'): ?> selected="true"<?php endif; ?>>北京时间 香港,新加坡,越南 (GMT +8)</option>
					<option value="Etc/GMT-9"<?php if($timezone=='Etc/GMT-9'): ?> selected="true"<?php endif; ?>>东京平壤时间 西伊里安,摩鹿加群岛 (GMT +9)</option>
					<option value="Etc/GMT-10"<?php if($timezone=='Etc/GMT-10'): ?> selected="true"<?php endif; ?>>悉尼关岛时间 塔斯马尼亚岛,新几内亚 (GMT +10)</option>
					<option value="Etc/GMT-11"<?php if($timezone=='Etc/GMT-11'): ?> selected="true"<?php endif; ?>>所罗门群岛 库页岛 (GMT +11)</option>
					<option value="Etc/GMT-12"<?php if($timezone=='Etc/GMT-12'): ?> selected="true"<?php endif; ?>>惠灵顿时间 新西兰,斐济群岛 (GMT +12)</option>
					<option value="Etc/GMT+1"<?php if($timezone=='Etc/GMT+1'): ?> selected="true"<?php endif; ?>>佛德尔群岛 亚速尔群岛,葡属几内亚 (GMT -1)</option>
					<option value="Etc/GMT+2"<?php if($timezone=='Etc/GMT+2'): ?> selected="true"<?php endif; ?>>大西洋中部时间 格陵兰 (GMT -2)</option>
					<option value="Etc/GMT+3"<?php if($timezone=='Etc/GMT+3'): ?> selected="true"<?php endif; ?>>布宜诺斯艾利斯 乌拉圭,法属圭亚那 (GMT -3)</option>
					<option value="Etc/GMT+4"<?php if($timezone=='Etc/GMT+4'): ?> selected="true"<?php endif; ?>>智利巴西 委内瑞拉,玻利维亚 (GMT -4)</option>
					<option value="Etc/GMT+5"<?php if($timezone=='Etc/GMT+5'): ?> selected="true"<?php endif; ?>>纽约渥太华 古巴,哥伦比亚,牙买加 (GMT -5)</option>
					<option value="Etc/GMT+6"<?php if($timezone=='Etc/GMT+6'): ?> selected="true"<?php endif; ?>>墨西哥城时间 洪都拉斯,危地马拉,哥斯达黎加 (GMT -6)</option>
					<option value="Etc/GMT+7"<?php if($timezone=='Etc/GMT+7'): ?> selected="true"<?php endif; ?>>美国丹佛时间 (GMT -7)</option>
					<option value="Etc/GMT+8"<?php if($timezone=='Etc/GMT+8'): ?> selected="true"<?php endif; ?>>美国旧金山时间 (GMT -8)</option>
					<option value="Etc/GMT+9"<?php if($timezone=='Etc/GMT+9'): ?> selected="true"<?php endif; ?>>阿拉斯加时间 (GMT -9)</option>
					<option value="Etc/GMT+10"<?php if($timezone=='Etc/GMT+10'): ?> selected="true"<?php endif; ?>>夏威夷群岛 (GMT -10)</option>
					<option value="Etc/GMT+11"<?php if($timezone=='Etc/GMT+11'): ?> selected="true"<?php endif; ?>>东萨摩亚群岛 (GMT -11)</option>
					<option value="Etc/GMT+12"<?php if($timezone=='Etc/GMT+12'): ?> selected="true"<?php endif; ?>>艾尼威托克岛 (GMT -12)</option>
					</select>
				</li>
				</ul>
			</li>
			<!--<li>
				<label class="add-post-label">允许上传的文件类型</label>
				<ul class="clearfix">
				<li>
					<input name="attachmentTypes[]" type="checkbox" value="@image@" id="attachmentTypes-@image@" checked="true">
					<label for="attachmentTypes-@image@">图片文件 <strong><small>gif jpg png tiff bmp</small></strong></label>
				</li>
				<li>
					<input name="attachmentTypes[]" type="checkbox" value="@media@" id="attachmentTypes-@media@">
					<label for="attachmentTypes-@media@">多媒体文件 <strong><small>mp3 wmv wma rmvb rm avi flv</small></strong></label>
				</li>
				<li>
					<input name="attachmentTypes[]" type="checkbox" value="@doc@" id="attachmentTypes-@doc@">
					<label for="attachmentTypes-@doc@">常用档案文件 <strong><small>txt doc docx xls xlsx ppt pptx zip rar pdf</small></strong></label>
				</li>
				<li>
					<input name="attachmentTypes[]" type="checkbox" value="@other@" id="attachmentTypes-@other@">
					<label for="attachmentTypes-@other@">其他格式 <input type="text" style="width: 250px;" name="attachmentTypesOther" value=""></label>
				</li>
				</ul>
				<p class="description">用逗号 "," 将后缀名隔开, 例如: cpp,h,mak</p>
			</li>-->
			<li>
				<input type="submit" id="advanced-settings-submit" value="<?php _e('Save Settings'); ?>" />
			</li>
			</ul>
		</form>
	</div>
</div>
<script>
$('#basic-settings').ajaxForm({
beforeSubmit: function(){
	$("#basic-settings-submit").attr('disabled',true);
},
success: function(data){
	data = eval('('+data+')');
	if( data.success ) {
		showMessage(data.message,'tips');
	} else {
		showMessage(data.message,'error');
	}
	$("#basic-settings-submit").attr('disabled',false);
}});
$('#advanced-settings').ajaxForm({
beforeSubmit: function(){
	$("#advanced-settings-submit").attr('disabled',true);
},
success: function(data){
	data = eval('('+data+')');
	if( data.success ) {
		showMessage(data.message,'tips');
	} else {
		showMessage(data.message,'error');
	}
	$("#advanced-settings-submit").attr('disabled',false);
}});
</script>
