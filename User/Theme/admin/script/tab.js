function initMenu() {
	$('#tabs').find('a.tabTop').each(function(){
		var self = $(this);
		$(this).click(function(){
			if( $(this).attr('href') != '#' ) {
				$('#content-wapper').remove();
				$('#tabs').find('a.tabTop').each(function(){
					$(this).parent().removeClass('select');
				});
				$(this).parent().addClass('select');
				$('body').append('<div id="content-wapper"><img src="'+PATH+'User/Theme/admin/image/loading.gif"></div>');
				$('#content-wapper').css('position','absolute');
				$('#content-wapper').css('top','57px');
				$('#content-wapper').css('opacity',0.5);
				$('#content-wapper').css('MozOpacity',0.5);
				$('#content-wapper').css('KhtmlOpacity',0.5);
				$('#content-wapper').css('filter', 'alpha(opacity=50)');
				$('#content-wapper').css('text-align','center');
				$('#content-wapper').css('background-color','#FFFFFF');
				$('#content-wapper').height( $('#content').height() + 10 );
				$('#content-wapper > img').css('margin-top',($('#content').height()-64)/2+'px');
				$('#content-wapper').css('width','100%');
				$.ajax({
					data : {'timestamp' : new Date().getTime()},
					url : $(this).attr('href'),
					type : 'get',
					timeout : 20 * 1000,
					success : function(response){
						$('#content-in').hide();
						$('#content-in').html(response);
						$('#content-in').show();
						var id = self.attr('id');
						id = id.split("_");
						window.location.hash = id[1];
						document.title = self.html() + ' ‹ ' + NAME;
						pageInit();
						showMessage('页面加载完成','tips');
					},
					error : function(){
						showMessage('页面加载失败','error');
					},
					complete : function(){
						$('#content-wapper').remove();
					}
				});
			}
			return false;
		});
	});
	$('#tabs').find('a.tabChild').each(function(){
		var self = $(this);
		$(this).click(function(){
			if( $(this).attr('href') != '#' ) {
				$('#content-wapper').remove();
				$('#tabs').find('a.tabTop').each(function(){
					$(this).parent().removeClass('select');
				});
				$(this).parent().parent().addClass('select');
				$('body').append('<div id="content-wapper"><img src="'+PATH+'User/Theme/admin/image/loading.gif"></div>');
				$('#content-wapper').css('position','absolute');
				$('#content-wapper').css('top','57px');
				$('#content-wapper').css('opacity',0.5);
				$('#content-wapper').css('MozOpacity',0.5);
				$('#content-wapper').css('KhtmlOpacity',0.5);
				$('#content-wapper').css('filter', 'alpha(opacity=50)');
				$('#content-wapper').css('text-align','center');
				$('#content-wapper').css('background-color','#FFFFFF');
				$('#content-wapper').height( $('#content').height() + 10 );
				$('#content-wapper > img').css('margin-top',($('#content').height()-64)/2+'px');
				$('#content-wapper').css('width','100%');
				$.ajax({
					data : {'timestamp' : new Date().getTime()},
					url : $(this).attr('href'),
					type : 'get',
					timeout : 20 * 1000,
					success : function(response){
						$('#content-in').hide();
						$('#content-in').html(response);
						$('#content-in').show();
						var id = self.attr('id');
						id = id.split("_");
						window.location.hash = id[1];
						document.title = self.html() + ' ‹ ' + NAME;
						pageInit();
						showMessage('页面加载完成','tips');
					},
					error : function(){
						showMessage('页面加载失败','error');
					},
					complete : function(){
						$('#content-wapper').remove();
					}
				});
			}
			return false;
		});
	});
	$('#tabs').children('li').each(function(){
		var self = $(this);
		$(this).hover(function(){
			if( $(this).children(':first-child').attr('href') == '#' ) {
				$.doTimeout( 'panel_hide' );
				$.doTimeout( 'panel_show', 500, function(){
					self.children('div').slideDown();
					return false;
				});
			}
		},function(){
			if( $(this).children(':first-child').attr('href') == '#' ) {
				$.doTimeout( 'panel_show' );
				$.doTimeout( 'panel_hide', 500, function(){
					self.children('div').slideUp();
					return false;
				});
			}
		});
	});
}

var Tabs = function (bar, className, index) {
	var gid = function (id) {return document.getElementById(id)},
		buttons = bar.getElementsByTagName('a'),
		selectButton = buttons[index],
		showContent = gid(selectButton.href.split('#')[1]),
		target;

	bar.onclick = function (event) {
		event = event || window.event;
		target = event.target || event.srcElement;
		
		if (target.nodeName.toLowerCase() === 'a') {
			showContent.style.display = 'none';
			showContent = gid(target.href.split('#')[1]);
			showContent.style.display = 'block';
			selectButton.className = '';
			selectButton = target;
			target.className = className;
			return false;
		};
	};
};

function pageInit() {
	$('.box').hover(function(){
		$(this).find('.box-title').css('border-bottom','2px solid #708EB0').css('color','#999999');
		$(this).find('.box-title h3').css('background-color','#708EB0').css('color','#FFFFFF');
	},function(){
		$(this).find('.box-title').css('border-bottom','2px solid #EBEBEB').css('color','#CCCCCC');
		$(this).find('.box-title h3').css('background-color','#EBEBEB').css('color','#708EB0');
	});
	$('.list-table tr input').click(function(){
		$(this).parent().click();
	});
	$('.list-table tr').hover(function(){
		if( $(this).find('input').attr("checked") != true ) {
			$(this).css('background-color','#C0F6FF');
		}
	},function(){
		if( $(this).find('input').attr("checked") != true ) {
			if( $(this).hasClass('even') ) {
				$(this).css('background-color','#E8EFF6');
			} else {
				$(this).css('background-color','#F1F8FF');
			}
		}
	}).click(function(){
		if( $(this).find('input').attr("checked") == true ) {
			if( $(this).hasClass('even') ) {
				$(this).css('background-color','#E8EFF6');
			} else {
				$(this).css('background-color','#F1F8FF');
			}
			$(this).find('input').removeAttr("checked");
		} else if( $(this).find('input').attr("checked") == false ) {
			if( $(this).hasClass('even') ) {
				$(this).css('background-color','#C8DFE6');
			} else {
				$(this).css('background-color','#D1E8EF');
			}
			$(this).find('input').attr("checked","true");
		}
	});

	if( typeof initEditor == "function" ) {
		initEditor();
	}
}

function pageLoad() {
	var str = window.location.href;
	var index = str.indexOf("#");
	if( index > 0 ) {
		str = str.substr(index);
		str = str.replace("#","");
		str = str.split("_");
		if( $('#h_'+str[0]).length == 0 ) {
			$('#tabs a.tabTop:first').click();
		} else {
			$('#h_'+str[0]).click();
		}
	} else {
		$('#tabs a.tabTop:first').click();
	}
}

function ajaxLoad( requestURL ) {
	$('#content-wapper').remove();
	$('body').append('<div id="content-wapper"><img src="'+PATH+'User/Theme/admin/image/loading.gif" /></div>');
	$('#content-wapper').css('position','absolute');
	$('#content-wapper').css('top','57px');
	$('#content-wapper').css('opacity',0.5);
	$('#content-wapper').css('MozOpacity',0.5);
	$('#content-wapper').css('KhtmlOpacity',0.5);
	$('#content-wapper').css('filter', 'alpha(opacity=50)');
	$('#content-wapper').css('text-align','center');
	$('#content-wapper').css('background-color','#FFFFFF');
	$('#content-wapper').height( $('#content').height() + 10 );
	$('#content-wapper > img').css('margin-top',($('#content').height()-64)/2+'px');
	$('#content-wapper').css('width','100%');
	$.ajax({
		data : {'timestamp' : new Date().getTime()},
		url : requestURL,
		type : 'get',
		timeout : 20 * 1000,
		success : function(response){
			$('#content-in').hide();
			$('#content-in').html(response);
			$('#content-in').show();
			pageInit();
			showMessage('页面加载完成','tips');
		},
		error : function(){
			showMessage('页面加载失败','error');
		},
		complete : function(){
			$('#content-wapper').remove();
		}
	});
}

var showMessageHandle;

function showMessage( msg, type ) {
	clearTimeout(showMessageHandle);
	msg = msg || '简单轻巧、优雅随心';
	type = type || 'default';
	$('#tips').removeClass('logx-tips');
	$('#tips').removeClass('logx-warning');
	$('#tips').removeClass('logx-error');
	switch( type ) {
		case 'tips':
			$('#tips').addClass('logx-tips');
			break;
		case 'warning':
			$('#tips').addClass('logx-waring');
			break;
		case 'error':
			$('#tips').addClass('logx-error');
			break;
	}
	$('#tips').html( msg );
	if( type != 'default' ) {
		showMessageHandle = setTimeout("showMessage(0,0)", 5000);
	}
}

function checkAll( id ) {
	$('#'+id).find('input').each(function(){
		if( $(this).attr("checked") != true ) {
			$(this).parent().parent().click();
		}
	});
}

function uncheckAll( id ) {
	$('#'+id).find('input').each(function(){
		if( $(this).attr("checked") == true ) {
			$(this).parent().parent().click();
		}
	});
}
