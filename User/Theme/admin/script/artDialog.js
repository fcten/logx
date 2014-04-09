/*
 * artDialog v2.1.1
 * Date: 2010-06-02
 * http://code.google.com/p/artdialog/
 * (c) 2009-2010 tangbin, http://www.planeArt.cn
 *
 * This is licensed under the GNU LGPL, version 2.1 or later.
 * For details, see: http://creativecommons.org/licenses/LGPL/2.1/
 */
(function() {
	this.art = {};

	var ini = function(c, y, n) {

		// artDialog('无配置参数的简写形式')
		if (typeof c === 'string') {
			var txt = c;
			c = {};
			c.content = txt;
			c.fixed = true;
		};

		if (c.id && $gid(c.id)) return newBox(c.id);// 如果ID存在则返回此ID对话框
		if (c.lock) c.fixed = true;					// 执行锁屏也同时执行静止定位
		if (c.menuBtn) c.fixed = false;				// 使用菜单模式就使用绝对定位
		if (typeof y === 'function') c.yesFn = y;
		if (typeof n === 'function') c.noFn = n;

		if (c.url) c.iframe = c.url; // 兼容<2.1版调用方式

		var config = {
			id: c.id,								// 对话框ID
			title: c.title || '\u63D0\u793A',		// 标题
			content: c.content,						// 普通消息
			iframe: c.iframe,						// iframe消息
			yesText: c.yesText || '\u786E\u5B9A',	// 确定按钮文本
			noText: c.noText || '\u53D6\u6D88',		// 取消按钮文本
			yesFn: c.yesFn,							// 确定按钮事件
			noFn: c.noFn,							// 取消按钮事件
			closeFn: c.noFn,						// 关闭按钮事件
			width: c.width,							// 宽度
			height: c.height,						// 高度
			menuBtn: c.menuBtn,						// 触发菜单模式的元素
			left: c.left,							// x轴坐标
			top: c.top,								// y轴坐标
			fixed: c.fixed,							// 是否静止定位
			style: c.style,							// 动态风格
			lock: c.lock,							// 是否锁屏
			time: c.time							// 限时关闭
		};

		return newBox(c.id).int(config);
	},
	
	alphaFx = 4,					// 锁屏遮罩透明渐变速度(等于1的时候不渐变,默认4)
	zIndex = 999999,				// 对话框初始叠加高度(重要：Opera浏览器z-index的最大值限制都小于其他浏览器)
	hideId = 'temp_artDialog',		// 用于预加载背景图的对话框ID
	bigBox = 100000,				// 指定超过此面积的对话框拖动的时候用替身,(等于0的时候全部用替身, 默认240000)
	closeText = '×',				// 关闭按钮文本
	loadText = 'Loading..',			// iframe加载提示文本
	pageLock = 0,

	M = {},
	tempBox,
	boxs = [],
	onmouse,
	inFocus,
	closeKey,
	IE = !-[1,], // !+'\v1',
	IE6 = IE && ([/MSIE (\d)\.0/i.exec(navigator.userAgent)][0][1] == 6),

	// 遍历
	Each = function(a, b) {
		for (var i = 0, len = a.length; i < len; i++) b(a[i], i);
	},

	// 获取页面尺寸相关数据
	// 如果要获取父页面或子页面的数据，请指定win参数
	getPage = function(win){
		var dd = win ? win.document.documentElement : document.documentElement,
			db = win ? win.document.body : document.body,
			dom = dd || db;
		return {
			width:  Math.max(dom.clientWidth, dom.scrollWidth),		// 页面宽度
			height: Math.max(dom.clientHeight, dom.scrollHeight),	// 页面长度
			left: Math.max(dd.scrollLeft, db.scrollLeft),			// 被滚动条卷去的文档宽度
			top: Math.max(dd.scrollTop, db.scrollTop),				// 被滚动条卷去的文档高度
			winWidth: dom.clientWidth,								// 浏览器视口宽度
			winHeight: dom.clientHeight								// 浏览器视口高度
		};
	},
	
	// 事件绑定
	bind = function (obj, type, fn) {
		if (obj.attachEvent) {
			obj['e' + type + fn] = fn;
			obj[type + fn] = function(){obj['e' + type + fn](window.event);}
			obj.attachEvent('on' + type, obj[type + fn]);
		} else {
			obj.addEventListener(type, fn, false);
		};
	},
	
	// 移除事件
	unbind = function (obj, type, fn) {
		if (obj.detachEvent) {
			try {
				obj.detachEvent('on' + type, obj[type + fn]);
				obj[type + fn] = null;
			} catch(_) {};
		} else {
			obj.removeEventListener(type, fn, false);
		};
	},

	// 阻止事件冒泡
	stopBubble = function(e){
		e.stopPropagation ? e.stopPropagation() : e.cancelBubble = true;
	},
	
	// 阻止浏览器默认行为
	stopDefault = function(e){
		e.preventDefault ? e.preventDefault() : e.returnValue = false;
	},

	// 清除文本选择
	clsSelect = function(){
		try{
			window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
		}catch(_){};
	},
	
	// 创建xhtml元素节点
	$ce = function (name){
		return document.createElement(name);
	},
	
	// 创建文本节点
	$ctn = function (txt){
		return document.createTextNode(txt);
	},

	// 用ID获取对象
	$gid = function (id){
		return document.getElementById(id);
	},

	// 用标签获取对象
	$gtag = function (tag) {
		return document.getElementsByTagName(tag);
	},
	
	// 判断CSS类是否存在
	hasClass = function(o, c){
		var reg = new RegExp('(\\s|^)'+c+'(\\s|$)');
		return o.className.match(reg);
	},
	
	// 添加CSS类
	addClass = function(o, c){
		if(!hasClass(o, c)){
			o.className += ' '+c;
		};
	},
	
	// 移除CSS类
	removeClass = function(o, c){
		if(hasClass(o, c)){
			var reg = new RegExp('(\\s|^)'+c+'(\\s|$)');
			o.className = o.className.replace(reg, ' ');
		};
	},
	
	// 向head添加CSS
	addStyle = function(s) {
		var T = this.style;
		if(!T){
			T = this.style = $ce('style');
			T.setAttribute('type', 'text/css');
			$gtag('head')[0].appendChild(T);
		};
		T.styleSheet && (T.styleSheet.cssText += s) || T.appendChild($ctn(s));
	},
	
	// 鼠标行为处理
	cmd = function(evt, x) {
		var e = evt || window.event,
			b = this.data.box,
			m = this.data.moveTemp,
			p = getPage(),
			i = this.security(b);

		M.box = this;
		this.zIndex();
		onmouse = true;
		M.x = e.clientX;
		M.y = e.clientY;
		M.top = parseInt(b.style.top);
		M.left = parseInt(b.style.left);
		M.width = b.offsetWidth;
		M.height = b.offsetHeight;
		M.winWidth = p.winWidth;
		M.winHeight = p.winHeight;
		M.pageWidth = p.width;
		M.pageHeight = p.height;
		M.pageLeft = p.left;
		M.pageTop = p.top;
		
		// 保存最大拖动范围限制数据
		M.maxX = i.maxX;
		M.maxY = i.maxY;
		
		var regular  = setInterval(function(){
			clsSelect();
		}, 40);
		clsSelect();
		
		//对于超过预计尺寸的对话框用替身代替拖动，保证流畅性
		if(M.width * M.height >= bigBox && !x) {
			tempBox = true;
			m.style.width = M.width + 'px';
			m.style.height = M.height + 'px';
			m.style.left = M.left + 'px';
			m.style.top = M.top + 'px';
			m.style.visibility = 'visible';
		};
		addClass(M.box.data.wrap, 'ui_move');
		addClass($gtag('html')[0], 'ui_page_move');

		document.onmousemove = function(a) {
			x ? resize.call(b, a, x) : drag.call(b, a);
		};
		
		document.onmouseup = function() {
			onmouse = false;
			document.onmouseup = null;
			if (document.body.releaseCapture) b.releaseCapture();// IE释放鼠标监控
			removeClass(M.box.data.wrap, 'ui_move');
			removeClass($gtag('html')[0], 'ui_page_move');
			clearInterval(regular);
			if (tempBox) {
				m.style.visibility = 'hidden';
				b.style.left = m.style.left;
				b.style.top = m.style.top;
				tempBox = false;
			};
		};
		
		// 让IE下鼠标超出视口仍可控制
		if (document.body.setCapture) {
			b.setCapture();
		};
	},

	// 拖动
	drag = function(a) {
		if (onmouse === false) return false;
		var e = a || window.event,
			obj = tempBox ? M.box.data.moveTemp : M.box.data.box,
			x = e.clientX,
			y = e.clientY,
			p = getPage(),
			l = parseInt(M.left - M.x + x - M.pageLeft + p.left),
			t = parseInt(M.top - M.y + y - M.pageTop + p.top);

		if (l > M.maxX) l = M.maxX;
		if (t > M.maxY ) t = M.maxY;	
		if (l < 0) l = 0;
		if (t < 0) t = 0;
		obj.style.left = l + 'px';
		obj.style.top = t + 'px';
		return false;
	},
	
	// 调整对话框大小
	resize = function(a, o) {
		if (onmouse === false) return false;
		var e = a || window.event,
		x = e.clientX,
		y = e.clientY,
		w = M.width + x - M.x + o.w,
		h = M.height + y - M.y + o.h;
		if (w > 0) o.obj.style.width = w + 'px';
		if (h > 0) o.obj.style.height = h + 'px';
		// 覆盖下拉控件的遮罩
		if (IE6) {
			M.box.data.selectMask.style.width = M.box.data.box.offsetWidth + 'px';
			M.box.data.selectMask.style.height = M.box.data.box.offsetHeight + 'px';
		};
		return false;
	},
	
	// 生产对话框
	newBox = function(id) {

		 // 循环利用
		var j = -1;
		Each(boxs, function(o, i) {
				if (id && o.data.wrap.id === id) {
					j = i;
				} else
				if (o.free === true){
					j = i;
				};
		});
		if (j >= 0) return boxs[j];
		
		// 
		// 	九宫格布局
		// 	
		// 	基于table 与 div,自适应
		// 
		var _title_wrap = $ce('td'),					// 标题栏
			_title = $ce('div'),						// 标题与按钮外套
			_title_text = $ce('div'),					// 标题文字内容
			_close = $ce('a');							// 关闭按钮
		_title_wrap.className = 'ui_title_wrap';
		_title.className = 'ui_title';
		_title_text.className = 'ui_title_text';
		_close.className = 'ui_close';
		_close.href = '#';
		_close.setAttribute('accesskey', 'c');
		_close.appendChild($ctn(closeText));
		_title.appendChild(_title_text);
		_title.appendChild(_close);
		_title_wrap.appendChild(_title);
		
		var _content_wrap = $ce('td'),					// 内容区
			_content = $ce('div'),
			_content_mask = $ce('div'),					// iframe内容遮罩
			_loading_tip = $ce('div');
		_loading_tip.className = 'ui_loading_tip';		// iframe加载提示
		_loading_tip.appendChild($ctn(loadText));
		_content_wrap.className = 'ui_content_wrap';
		_content.className = 'ui_content';
		_content_mask.className = 'ui_content_mask';
		_content_wrap.appendChild(_content);
		_content_wrap.appendChild(_loading_tip);
		
		var _yesBtn = $ce('button'),					// 确定按钮
			_yesWrap = $ce('span'),
			_noBtn = $ce('button'),						// 取消按钮
			_noWrap = $ce('span');
		_yesBtn.setAttribute('accesskey', 'y');
		_yesWrap.className = 'ui_yes';
		_noBtn.setAttribute('accesskey', 'n');
		_noWrap.className = 'ui_no';
		
		var _bottom_wrap = $ce('td'),					// 底部按钮区
			_bottom = $ce('div'),
			_btns = $ce('div'),
			_resize = $ce('div');						// 调节对话框大小的手柄
		_bottom_wrap.className = 'ui_bottom_wrap';
		_bottom.className = 'ui_bottom';
		_btns.className = 'ui_btns';
		_resize.className = 'ui_resize';
		_bottom.appendChild(_btns);
		_bottom.appendChild(_resize);
		_bottom_wrap.appendChild(_bottom);
		
		var _dialog_main = $ce('table'),				// 内容表格
			_cTbody = $ce('tbody');
		_dialog_main.className = 'ui_dialog_main';
		for(var r = 0; r < 3; r++){
			var _tr = $ce('tr');
			if (r == 0) _tr.appendChild(_title_wrap);
			if (r == 1) _tr.appendChild(_content_wrap);
			if (r == 2) _tr.appendChild(_bottom_wrap);
			_cTbody.appendChild(_tr);
		};
		_dialog_main.appendChild(_cTbody);
		
		var _bTable = $ce('table'),						// 外边框表格
			_bTbody = $ce('tbody');
		for(var r=0;r<3;r++){
			var _tr = $ce('tr');
			for(var d=0; d<3; d++){
				var _td = $ce('td');
				if(r == 1 && d == 1) {
					_td.className = 'ui_td_' + r + d;
					_td.appendChild(_dialog_main);
				}else{
					_td.className = 'ui_border ' + 'ui_td_' + r + d;
				};
				_tr.appendChild(_td);
			};
			_bTbody.appendChild(_tr);
		};
		_bTable.appendChild(_bTbody);

		var _dialog = $ce('div');						// 对话框
		_dialog.className = 'ui_dialog';
		if (IE6) {
			var _ie6_select_mask = $ce('iframe');		// 使用一个iframe覆盖IE6下拉控件
			_ie6_select_mask.className = 'ui_ie6_select_mask';
			_dialog.appendChild(_ie6_select_mask);
		};
		_dialog.appendChild(_bTable);

		var _overlay = $ce('div');						// 锁屏遮罩
		_overlay.className = 'ui_overlay';
		_overlay.appendChild($ce('div'));

		var _move_temp = $ce('div');					// 对话框移动状态的替身
		_move_temp.className = 'ui_move_temp';
		_move_temp.appendChild($ce('div'));
		document.body.appendChild(_move_temp);
		
		var _dialog_wrap = $ce('div');					// 对话框外套
		_dialog_wrap.className = 'ui_dialog_wrap';
		_dialog_wrap.appendChild(_overlay);
		_dialog_wrap.appendChild(_dialog);
		_dialog_wrap.appendChild(_move_temp);
		/*九宫格布局结束*/

		
		// 触发拖动函数
		_title_text.onmousedown = function(a) {
			cmd.call($, a, false);
			return false;
		};
		
		// 触发调节大小函数
		_resize.onmousedown = function(a) {
			var d = _dialog,
			c = _content_wrap;
			cmd.call($, a, {obj:c, w:c.offsetWidth - d.offsetWidth, h:c.offsetHeight - d.offsetHeight});
			return false;
		};
		
		// 给定Tab切换焦点的按钮的值
		_yesBtn.onfocus = _yesBtn.onblur = function(){
			$.data.btnTab = _noBtn;
		};
		_noBtn.onfocus = _noBtn.onblur = function(){
			$.data.btnTab = _yesBtn;
		};
		
		// 向页面插入对话框
		document.body.appendChild(_dialog_wrap);
		
		var $ = {

			// 数据缓存
			data: {
				box: _dialog,
				moveTemp: _move_temp,
				selectMask: _ie6_select_mask,
				wrap: _dialog_wrap
			},
			
			int: function(c){
				$.data.config = c;

				if (typeof c.id === 'string') _dialog_wrap.id = c.id;
				if (typeof c.style === 'string') _bTable.className = c.style;

				$.content(c.title, c.content, c.iframe).
				yesBtn(c.yesFn, c.yesText).
				noBtn(c.noFn, c.noText).
				closeBtn(c.closeFn).
				size(c.width, c.height).
				align(c.menuBtn, c.left, c.top, c.fixed);

				if (c.lock) $.lock.show();
				if (c.time) $.time(c.time);

				return $;
			},
			
			
			// 消息内容构建(标题, HTML内容, iframe)
			content: function(title, content, iframe) {
				$.free = false;
				$.data.content = _content;// 存储内容容器

				if (content) {
					_content.innerHTML = '<span class="ui_dialog_icon"></span>' + content;
					$.btnFocus();
				} else
				
				if (iframe) {
					$.loading.show();

					$._iframe = $ce('iframe');
					$._iframe.setAttribute('frameborder', 0, 0); // 消除IE7可能会出现的边框
					$._iframe.src = iframe;
					addClass(_dialog_wrap, 'ui_iframe');
					_content.appendChild($._iframe);
					_content.appendChild(_content_mask);
					
					// iframe加载完毕
					$.data.iframeLoad = function(){
						var c = $.data.config;
						$.loading.hide();

						if (!c.width && !c.height) try{
							var i = getPage($._iframe.contentWindow);
							$.size(i.width, i.height);
						}catch (_){};

						$._iframe.style.cssText = 'width:100%;height:100%'; // 自适应宽度需要最后设置，否则IE6、7无法正确获取iframe实际尺寸
						if (!c.left && !c.top) $.center();
						$.btnFocus();
						
					};
					bind($._iframe, 'load', $.data.iframeLoad);

					// 存储iframe内容对象
					$.data.iframe = $._iframe.contentWindow || $._iframe;
				} else {
					return $;
				};

				_title_text.innerHTML = '<span class="ui_title_icon"></span>' + title;
				_dialog_wrap.style.visibility = 'visible';
				return $;
			},
			
			// 尺寸(宽度, 高度)
			size: function(w, h) {
				if(parseInt(w) == w) w = w + 'px';
				if(parseInt(h) == h) h = h + 'px';
				_content_wrap.style.width = w || '';
				_content_wrap.style.height = h || '';
				
				// 覆盖下拉控件的遮罩
				if (IE6) {
					_ie6_select_mask.style.width = _dialog.offsetWidth;
					_ie6_select_mask.style.height = _dialog.offsetHeight;
				};
				
				return $;
			},
			
			// 对话框安全范围计算
			security: function(obj){
				var minX, minY, maxX, maxY, centerX, centerY;

				$.data.boxWidth = obj.offsetWidth;
				$.data.boxHeight = obj.offsetHeight;
				var p = getPage();
				M.winWidth = p.winWidth;
				M.winHeight = p.winHeight;
				M.pageWidth = p.width;
				M.pageHeight = p.height;
				M.pageLeft = p.left;
				M.pageTop = p.top;
				
				if ($.data.config.fixed) {
					minX = 0;
					maxX = M.winWidth - $.data.boxWidth;
					centerX = maxX / 2;
					minY = 0;
					maxY = M.winHeight - $.data.boxHeight;
					// 小对话框在视觉黄金比例垂直居中，大对话框绝对居中
					var hc =  M.winHeight * 0.382 - $.data.boxHeight / 2;
					centerY = ($.data.boxHeight < M.winHeight / 2) ?  hc : maxY / 2;
				} else {
					minX = M.pageLeft;
					maxX = M.winWidth + minX - $.data.boxWidth;
					centerX = maxX / 2;
					minY = M.pageTop;
					maxY = M.winHeight + minY - $.data.boxHeight;
					// 黄金比例垂直居中
					var hc = M.winHeight * 0.382 - $.data.boxHeight / 2 + minY;
					centerY =  ($.data.boxHeight < M.winHeight / 2) ? hc : (maxY + minY) / 2;
				};
				if (centerX < 0) centerX = 0;
				if (centerY < 0) centerY = 0;
				return {minX: minX, minY: minY, maxX: maxX, maxY: maxY, centerX: centerX, centerY: centerY};
			},
			
			// 居中对齐
			center: function(){
				 var t = $.security(_dialog)
				_dialog.style.left = t.centerX + 'px';
				_dialog.style.top = t.centerY + 'px';
				return $;
			},

			// 位置(指定元素附近弹出, 横坐标, 纵坐标, 是否静止定位)
			align: function(menuBtn, left, top, fixed) {
				var t = $.security(_dialog);

				// 获取指定对象的坐标，让对话框在按钮附近当作菜单弹出
				if (menuBtn && menuBtn.getBoundingClientRect) {
					var w = $.data.boxWidth / 2 - menuBtn.offsetWidth / 2,
					h = menuBtn.offsetHeight,
					ml = menuBtn.getBoundingClientRect().left,
					mt = menuBtn.getBoundingClientRect().top;

					if (w > ml) w = 0;
					if (mt + h > M.winHeight - $.data.boxHeight) h = - $.data.boxHeight;

					left = ml + M.pageLeft/* - w*/;
					top = mt + M.pageTop + h;
				};

				if (fixed) {
					if (IE6) addClass($gtag('html')[0], 'ui_ie6_fixed');// 关闭对话框不要清除此类，它只用来消除IE6抖动的问题
					addClass(_dialog_wrap, 'ui_fixed');
				};

				if(!left){
					$.data.boxLeft = t.centerX;
				}else if(left == 'left'){
					$.data.boxLeft = t.minX;
				}else if(left == 'right'){
					$.data.boxLeft = t.maxX;
				}else{
					left = fixed ? left - M.pageLeft : left;// 把原点移到浏览器视口
					left = left < t.minX ? t.minX : left;
					left = left > t.maxX ? t.maxX : left;
					$.data.boxLeft = left;
				};
				if (!top){
					$.data.boxTop = t.centerY;
				} else if (top == 'top'){
					$.data.boxTop = t.minY;
				} else if (top == 'bottom'){
					$.data.boxTop = t.maxY;
				} else {
					top = fixed ? top - M.pageTop : top;// 把原点移到浏览器视口
					top = top < t.minY ? t.minY : top;
					top = top > t.maxY ? t.maxY : top;
					$.data.boxTop = top;
				};
				
				if (_dialog_wrap.id == hideId) $.data.boxLeft = '-99999';// 让预加载背景图的对话框偏离可视范围
				_dialog.style.left = $.data.boxLeft + 'px';
				_dialog.style.top = $.data.boxTop + 'px';
				$.zIndex(_dialog);
				
				return $;
			},

			// 确定按钮(回调函数, 按钮文本)
			yesBtn: function(fn, txt){
				if (typeof fn === 'function') {
					_yesBtn.innerHTML = txt;
					_yesWrap.appendChild(_yesBtn);
					_btns.appendChild(_yesWrap);
					_yesBtn.onclick = function() {
						var f = fn();
						if (f != false) $.close();// 如果回调函数返回false则不关闭对话框
					};
					
					// 给确定按钮添加一个 Ctrl + Enter 快捷键
					_dialog.onkeyup = function(evt){
						var e = evt || window.event;
						if(e.ctrlKey && e.keyCode == 13) _yesBtn.click();
					};
				};
				return $;
			},

			// 取消按钮(回调函数, 按钮文本)
			noBtn: function(fn, txt){
				if (typeof fn === 'function') {
					_noBtn.innerHTML = txt;
					_noWrap.appendChild(_noBtn);
					_btns.appendChild(_noWrap);
					_noBtn.onclick = function() {
						var f = fn();
						if (f != false) $.close();// 如果回调函数返回false则不关闭对话框
					};
				};
				return $;
			},

			// 关闭按钮(回调函数)
			closeBtn: function(fn){
				_close.onclick = function(){
					if (typeof fn === 'function') {
						var f = fn();
						if (f != false) $.close();// 如果回调函数返回false则不关闭对话框
					} else {
						$.close();
					};
					return false;
				};
				return $;
			},

			// 焦点定位
			btnFocus: function(){
				setTimeout(function(){
					try{
						if ($.data.config.noFn) {
							_noBtn.focus();
						} else
						if ($.data.config.yesFn) {
							_yesBtn.focus();
						} else {
							_close.focus();
						};
					}catch(_){};
				}, 40);
				return $;
			},

			// 关闭对话框(回调函数)
			close: function(f) {
				if (f) {
					if (typeof f === 'function') $.data.closeFn = f;
					return $;
				};

				// 执行回调函数
				if ($.data.closeFn) {
					var cfn = $.data.closeFn();
					if (cfn != false) {
						$.data.closeFn = null;
					} else {
						return $;
					};
				};
				
				_bTable.className = _dialog.style.cssText = _title_text.innerHTML = _content.innerHTML = _btns.innerHTML = _dialog_wrap.id = '';// 设置复位
				Each(['ui_fixed', 'ui_loading', 'ui_focus', 'ui_iframe'], function(o, i) {
						removeClass(_dialog_wrap, o);
				});
				_dialog_wrap.style.visibility = 'hidden';

				$._iframe = null;

				$.lock.hide();
				onmouse = false;
				$.free = true;
			},
			
			// 定时关闭对话框(秒)
			time: function(t) {
				if (typeof t === 'number') setTimeout(function(){
					$.close();
				}, 1000 * t);
				return $;
			},
			

			// 对话框叠加高度
			zIndex: function() {
				zIndex++;
				_dialog.style.zIndex = _overlay.style.zIndex = _dialog_wrap.style.zIndex = zIndex; //_dialog_wrap: IE6与Opera叠加高度受具有绝对或者相对定位的父元素z-index控制
				_move_temp.style.zIndex = zIndex + 1;
				
				// 当出现多个对话框时，让顶层对话框显得与众不同
				if (inFocus) removeClass(inFocus, 'ui_focus');
				addClass(_dialog_wrap, 'ui_focus');
				inFocus = _dialog_wrap;
				
				// 定义ESC键关闭最高的弹出层
				if (closeKey) unbind(document, 'keyup', closeKey);
				closeKey = function(evt){
					var e = evt || window.event;
					if (e.keyCode == 27) _close.onclick();
				};
				bind(document, 'keyup', closeKey);
				
				return $;
			},

			// 显示加载提示
			loading: {
				show: function(){
					addClass(_dialog_wrap, 'ui_loading');
					return $;
				},
				
				hide: function(){
					removeClass(_dialog_wrap, 'ui_loading');
					return $;
				}
			},
			
			// 锁屏
			lock: {
				show: function(){
					if (pageLock >= 1) return $;// 遮罩数量限制(IE只支持一个)

					var h = $gtag('html')[0];
					addClass(_dialog_wrap, 'ui_lock');
					addClass(h, 'ui_page_lock');
					$.zIndex(_overlay);

					// 让Tab等键在对话框中仍然能使用
					_dialog.onkeydown = function(evt){
						var e = evt || window.event,
							key = e.keyCode;
						if (key == 9 || key == 38 || key == 40) stopBubble(e);
					};

					// 让右键在对话框中仍然能使用
					_dialog.oncontextmenu = function(evt){
						var e = evt || window.event;
						stopBubble(e);
					};

					// 页面鼠标操作限制
					var p = getPage();
					M.pageLeft = p.left,
					M.pageTop = p.top;
					$.data.lockMouse = function(evt){
						var e = evt || window.event;
						stopBubble(e);
						stopDefault(e);
						scroll(M.pageLeft, M.pageTop);
					};
					Each(['DOMMouseScroll', 'mousewheel', 'scroll'], function(o, i) {
							bind(document, o, $.data.lockMouse);
					});

					// 屏蔽特定按键: F5, Ctrl + R, Ctrl + A, Tab, Up, Down
					$.data.lockKey = function(evt){
						var e = evt || window.event,
							key = e.keyCode;
						
						// 切换按钮焦点
						if (key == 37 || key == 39 || key == 9) {
							try{
								$.data.btnTab.focus();
							}catch(_){};
						};

						if((key == 116) || (e.ctrlKey && key == 82) || (e.ctrlKey && key == 65) || (key == 9) || (key == 38) || (key == 40)) {
							try{
								e.keyCode = 0;// 阻止F5键默认行为需要加上这句(IE8测试)	
							}catch(_){};
							stopDefault(e);
						};
					};
					bind(document, 'keydown', $.data.lockKey);

					_overlay.onclick = _overlay.oncontextmenu = function(){
						$.btnFocus();
						return false;
					};
					
					$.alpha(_overlay, 0, function(){
						pageLock ++;
					});

					return $;
				},
				
				// 关闭锁屏
				hide: function(){
					if (_dialog_wrap.className.indexOf('ui_lock') > -1){
						$.alpha(_overlay, 1, function(){
							removeClass(_dialog_wrap, 'ui_lock');
							if (pageLock == 1) removeClass($gtag('html')[0], 'ui_page_lock');// 移除顶级页面锁屏样式
							Each(['DOMMouseScroll', 'mousewheel', 'scroll', 'contextmenu'], function(o, i) {
								unbind(document, o, $.data.lockMouse);		// 解除页面鼠标操作限制
							});
							unbind(document, 'keydown', $.data.lockKey);	// 解除屏蔽的按键
							pageLock --;
						});
					};

					return $;
				}
			},
			
			// 透明渐变(元素, 初始透明值[0,1], 回调函数)
			alpha: function(obj, int, fn){
				var m = obj.filters ? 100 : 1,		// 最大值
					s = m / alphaFx;				// 速度

				s = int == 0 ? s : -s;
				int = (obj.filters && int == 1) ? 100 : int;

				var fx = function(){
					int = int + s;
					obj.filters ? obj.filters.alpha.opacity = int : obj.style.opacity = int;

					if (0 >= int || int >= m) {
						if (fn) fn();
						clearInterval($.data.startFx);
					};
				};
				fx();

				clearInterval($.data.startFx);
				$.data.startFx = setInterval(fx, 40);

				return $;
			}

		};

		
		// 保存对话框列队
		return boxs[boxs.push($) - 1];
	};
	
	// 
	// 	artDialog兼容框架样式[内部版本1.3 2010-06-02]
	// 	
	// 
	addStyle('.ui_dialog_wrap{visibility:hidden}.ui_title_icon,.ui_content,.ui_dialog_icon,.ui_btns span{display:inline-block;*zoom:1;*display:inline}.ui_dialog{text-align:left;position:absolute;top:0}.ui_dialog table{border:0;margin:0;border-collapse:collapse}.ui_dialog td{padding:0}.ui_title_icon,.ui_dialog_icon{vertical-align:middle;_font-size:0}.ui_title_text{overflow:hidden;cursor:default}.ui_close{display:block;position:absolute;outline:none}.ui_content_wrap{text-align:center}.ui_content{margin:10px;text-align:left}.ui_iframe .ui_content{margin:0;*padding:0;display:block;height:100%;position:relative}.ui_iframe .ui_content iframe{border:none;overflow:auto}.ui_content_mask {visibility:hidden;width:100%;height:100%;position:absolute;top:0;left:0;background:#FFF;filter:alpha(opacity=0);opacity:0}.ui_bottom{position:relative}.ui_resize{position:absolute;right:0;bottom:0;z-index:1;cursor:nw-resize;_font-size:0}.ui_btns{text-align:right;white-space:nowrap}.ui_btns span{margin:5px 10px}.ui_btns button{cursor:pointer}* .ui_ie6_select_mask{position:absolute;top:0;left:0;z-index:-1;filter:alpha(opacity=0)}.ui_loading .ui_content_wrap{position:relative;min-width:9em;min-height:3.438em}.ui_loading .ui_btns{display:none}.ui_loading_tip{visibility:hidden;width:5em;height:1.2em;text-align:center;line-height:1.2em;position:absolute;top:50%;left:50%;margin:-0.6em 0 0 -2.5em}.ui_loading .ui_loading_tip,.ui_loading .ui_content_mask{visibility:visible}.ui_loading .ui_content_mask{filter:alpha(opacity=100);opacity:1}.ui_move .ui_title_text{cursor:move}.ui_page_move .ui_content_mask{visibility:visible}.ui_move_temp{visibility:hidden;position:absolute;cursor:move}.ui_move_temp div{height:100%}html>body .ui_fixed .ui_move_temp{position:fixed}html>body .ui_fixed .ui_dialog{position:fixed}* .ui_ie6_fixed{background:url(*) fixed}* .ui_ie6_fixed body{height:100%}* html .ui_fixed{width:100%;height:100%;position:absolute;left:expression(documentElement.scrollLeft+documentElement.clientWidth-this.offsetWidth);top:expression(documentElement.scrollTop+documentElement.clientHeight-this.offsetHeight)}* .ui_page_lock select,* .ui_page_lock .ui_ie6_select_mask{visibility:hidden}.ui_overlay{visibility:hidden;_display:none;position:fixed;top:0;left:0;width:100%;height:100%;filter:alpha(opacity=0);opacity:0;_overflow:hidden}.ui_lock .ui_overlay{visibility:visible;_display:block}.ui_overlay div{height:100%}* html body{margin:0}');
	
	if (IE6) document.execCommand('BackgroundImageCache', false, true);// 开启IE CSS背景图片缓存
	
	// 页面载入即启动一个隐秘对话框，让浏览器预先加载皮肤背景图片
	bind(window, 'load', function(){
		if (!inFocus) artDialog({id:hideId, style:'confirm alert error succeed', time:10}, function(){}, function(){});
	});
	
	// 对话框接口
	art.dialog = ini;
	this.artDialog = ini;// 兼容老版本调用方式
})();
