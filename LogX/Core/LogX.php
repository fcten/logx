<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class LogX {

	// LogX 运行过程中的一些全局信息
	public static $_globalVars;

	/**
	 * @brief init LogX 全局初始化方法
	 *
	 * @return void
	 */
	public static function init() {
		// 输出 Logo
		if ( isset( $_GET['591E-D5FC-8065-CD36-D3E8-E45C-DB86-9197'] ) ) {
			Response::logo();
		}

		// 非 DEBUG 模式下关闭错误输出
		if( defined( 'LOGX_DEBUG' ) ) {
			error_reporting( E_ALL );
		} else {
			error_reporting( 0 );
		}

		// 设置自动载入函数
		function __autoLoad( $className ) {
			if( substr( $className, -7 ) == 'Library' && is_file( LOGX_LIB . $className . '.php' ) ) {
				@require_once LOGX_LIB . $className . '.php';
			}
		}

		// 设置错误与异常处理函数
		set_error_handler( array( __CLASS__, 'error' ) );
		set_exception_handler( array( __CLASS__, 'exception' ) );

		// 运行环境检查
		if ( ! version_compare( PHP_VERSION, '5.0.0', '>=' ) ) {
			throw new LogXException( sprintf( _t('LogX needs PHP 5.0.x or higher to run. You are currently running PHP %s.'), PHP_VERSION ) );
		}

		if ( ! version_compare( PHP_VERSION, '5.2.0', '>=' ) ) {
			// 针对低版本 PHP 的兼容代码
			@require_once LOGX_CORE . 'Compat.php';
		}

		// 设置语言
		if( defined( 'LOGX_LANGUAGE' ) ) {
			Language::set( LOGX_LANGUAGE );
		} else {
			Language::set( 'zh-cn' );
		}

		// 预编译核心文件
		global $coreFiles;
		if( !defined( 'LOGX_DEBUG' ) && !file_exists( LOGX_CACHE . '~core.php' ) ) {
			Compile::build( LOGX_CACHE, $coreFiles, 'core' );
		} elseif( !defined( 'LOGX_DEBUG' ) ) {
			$file_time = filemtime( LOGX_CACHE . '~core.php' );
			foreach( $coreFiles as $file ) {
				if( filemtime( $file ) > $file_time ) {
					Compile::build( LOGX_CACHE, $coreFiles, 'core' );
					break;
				}
			}
		}

		self::$_globalVars = array(
			'RUN' => array(
				'TIME' => microtime( TRUE ),
				'MEM'  => function_exists( 'memory_get_usage' ) ? memory_get_usage() : 0,
				'LANG' => 'zh-cn',
			),
			// 系统环境
			'SYSTEM' => array(
				'OS'    => PHP_OS,
				'HTTP'  => Request::S('SERVER_SOFTWARE','string'),
				'PHP'   => PHP_VERSION,
				'MYSQL' => '', /*此时尚未连接到数据库*/
			),
			// PHP 扩展支持
			'SUPPORT' => array(
				'MYSQL'    => function_exists( 'mysql_connect' ),
				'GD'       => function_exists( 'imagecreate' ),
				'MEMCACHE' => function_exists( 'memcache_connect' ),
				'SHMOP'    => function_exists( 'shmop_open' ),
				'GZIP'     => function_exists( 'ob_gzhandler' ),
				'TIMEZONE' => function_exists( 'date_default_timezone_set' ),
				'AUTOLOAD' => function_exists( 'spl_autoload_register' ),
			),
			// PHP.ini 检查
			'INI' => array(
				'ALLOW_CALL_TIME_PASS_REFERENCE' => ini_get('allow_call_time_pass_reference'),
				'MAGIC_QUOTES_GPC'               => ini_get('magic_quotes_gpc'),
				'REGISTER_GLOBALS'               => ini_get('register_globals'),
				'ALLOW_URL_FOPEN'                => ini_get('allow_url_fopen'),
				'ALLOW_URL_INCLUDE'              => ini_get('allow_url_include'),
				'SAFE_MODE'                      => ini_get('safe_mode'),
				'MAX_EXECUTION_TIME'             => ini_get('max_execution_time'),
				'MEMORY_LIMIT'                   => ini_get('memory_limit'),
				'POST_MAX_SIZE'                  => ini_get('post_max_size'),
				'FILE_UPLOADS'                   => ini_get('file_uploads'),
				'UPLOAD_MAX_FILESIZE'            => ini_get('upload_max_filesize'),
				'MAX_FILE_UPLOADS'               => ini_get('max_file_uploads'),
			)
		);

		// 清除不需要的变量，防止变量注入
		$defined_vars = get_defined_vars();
		foreach ( $defined_vars as $key => $value ) {
			if ( !in_array( $key, array( '_POST','_GET','_COOKIE','_SERVER','_FILES' ) ) ) {
				${$key} = '';
				unset( ${$key} );
			}
		}

		// 对用户输入进行转义处理
		if ( !get_magic_quotes_gpc() ) {
			$_GET = self::addSlashes( $_GET );
			$_POST = self::addSlashes( $_POST );
			$_COOKIE = self::addSlashes( $_COOKIE );
		}

		// 开启输出缓存
		if ( defined( 'LOGX_GZIP' ) && self::$_globalVars['SUPPORT']['GZIP'] ) {
			ob_start( 'ob_gzhandler' );
		} else {
			ob_start();
		}

		// 连接到数据库
		Database::connect( DB_HOST, DB_USER, DB_PWD, DB_NAME, DB_PCONNECT );
		self::$_globalVars['SYSTEM']['MYSQL'] = Database::version();

		// 设定时区
		if( self::$_globalVars['SUPPORT']['TIMEZONE'] ) {
			date_default_timezone_set( OptionLibrary::get('timezone') );
		}

		// 连接到缓存
		Cache::connect( CACHE_TYPE );

		// 初始化路由表
		Router::init();

		// 初始化主题控制器
		Theme::init();

		// 初始化 Plugin
		Plugin::initPlugins();

		// 初始化全局组件
		Widget::initWidget('Global');
		Widget::initWidget('Widget');
		Widget::initWidget('Page');
		Widget::initWidget('User');

		// 尝试自动登录
		Widget::getWidget('User')->autoLogin();

		// 启动路由分发
		Router::dispatch();
	}

	/**
	 * @brief error 错误处理函数
	 *
	 * @param $errno 错误码
	 * @param $errstr 错误信息
	 * @param $errfile 错误文件
	 * @param $errline 错误行数
	 *
	 * @return void
	 */
	public static function error( $errno, $errstr, $errfile, $errline ) {
		if( defined( 'LOGX_DEBUG' ) ) {
			// 直接抛出异常
			throw new LogXErrorException( $errstr, 0, $errno, $errfile, $errline );
		} else {
			Response::error( 500 );
		}
	}

	/**
	 * @brief exception 异常处理函数
	 *
	 * @param $e 异常对象
	 *
	 * @return void
	 */
	public static function exception( $e ) {
		if( defined( 'LOGX_DEBUG' ) ) {
			$e->__toString();
		} else {
			Response::error( 500 );
		}
	}

	/**
	 * @brief addSlashes 字符串转义
	 *
	 * @param $string 输入字符串
	 *
	 * @return string
	 */
	public static function addSlashes( $string ) {
		if ( !is_array( $string ) ) return addslashes( $string );
		foreach ( $string as $key => $val ) {
			$string[$key] = self::addSlashes( $val );
		}
		return $string;
	}

	/**
	 * @brief delSlashes 字符串反转义
	 *
	 * @param $string 输入字符串
	 *
	 * @return string
	 */
	public static function delSlashes( $string ) {
		if ( !is_array( $string ) ) return stripslashes( $string );
		foreach ( $string as $key => $val ) {
			$string[$key] = self::delSlashes( $val );
		}
		return $string;
	}

	/**
	 * @brief randomString 获取指定长度的随机字符串
	 *
	 * @param $len 长度
	 *
	 * @return string
	 */
	public static function randomString( $len ) {
		$chars = array(
			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", 
			"l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", 
			"w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", 
			"H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", 
			"S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2", 
			"3", "4", "5", "6", "7", "8", "9");
		$charsLen = count($chars) - 1;

		// 将数组打乱
		shuffle($chars);
    
		$output = "";
		for ($i=0; $i<$len; $i++) {
			$output .= $chars[mt_rand(0, $charsLen)];
		}  
		return $output;
	}

	/**
	 * @brief readFile 读取指定目录下的指定后缀文件
	 *
	 * @param $directory 目录名
	 * @param $ext 文件后缀
	 *
	 * @return array
	 */
	public static function readFile( $directory, $ext = '' ) {
		$files = array();
		if ( is_dir( $directory ) ) {
			$handle = opendir( $directory );
			while ( $file = readdir( $handle ) ){
				$subdir = $directory .$file;
				if ( is_file( $subdir ) ){
					if( $ext == '' ) {
			    			$files[] = $directory . $file;
			    		} else {
						$fileInfo = pathinfo($subdir);
						$fileExt = $fileInfo['extension'];
						if ( $fileExt == $ext ) {
							$files[] = $directory . $file;
						}
					}
				}
			}
			closedir( $handle );
		} else {
			Log::add( '[' . __FILE__ . '] [' . __LINE__ . '] ' . sprintf( _t('Use illegal directory name: %s.' ), $directory ), E_USER_NOTICE );
		}
		return $files;
	}

	/**
	 * @brief readDir 读取指定目录下的所有子目录
	 *
	 * @param $path 目录名
	 *
	 * @return array
	 */
	public static function readDir( $path ) {
		$dirs = array();
		if( is_dir( $path ) ) {
			if( $d = opendir( $path ) ) {
				while( ($dir = readdir( $d ) ) !== false ) {
					if( is_dir( $path . $dir ) && $dir != '.' && $dir != '..' ) {
						$dirs[] = $path . $dir;
					}
				}
			}
			@closedir( $d );
		} else {
			if( defined( 'LOGX_DEBUG' ) ) {
				Log::add( '[' . __FILE__ . '] [' . __LINE__ . '] ' . _t( 'Use illegal directory name: %s.' ) . $path, E_USER_NOTICE );
			}
		}
		return $dirs;
	}

	/**
	 * @brief countDirSize 计算目录大小
	 *
	 * @param $dir 目录位置
	 *
	 * @return int
	 */
	public static function countDirSize( $dir ) {
		$sizeResult = 0;
		$handle = opendir( $dir );
		while ( false !== ( $FolderOrFile = readdir( $handle ) ) ) {
			if( $FolderOrFile != "." && $FolderOrFile != ".." ) {
				if( is_dir( "$dir/$FolderOrFile" ) ) {
					$sizeResult += self::countDirSize("$dir/$FolderOrFile");
				} else {
					$sizeResult += filesize("$dir/$FolderOrFile");
				}
			} 
		}
		closedir($handle);
		return $sizeResult;
	}

	/**
	 * @brief cutStr 字符串截取
	 *
	 * @param $text 原字符串
	 * @param $limit 截取长度
	 * @param $start 开始截取位置
	 *
	 * @return string
	 */
	public static function cutStr( $text, $limit = 12, $start = 0 ) {
		if (function_exists('mb_substr')) {
			$more = (mb_strlen($text,'UTF-8') > $limit) ? TRUE : FALSE;
			$text = mb_substr($text, 0, $limit, 'UTF-8');
		} elseif (function_exists('iconv_substr')) {
			$more = (iconv_strlen($text,'UTF-8') > $limit) ? TRUE : FALSE;
			$text = iconv_substr($text, 0, $limit, 'UTF-8');
		} else {
			preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);
			if(func_num_args() >= 3) {
				if (count($ar[0])>$limit) {
					$more = TRUE;
					$text = join("",array_slice($ar[0],0,$limit));
				} else {
					$more = FALSE;
					$text = join("",array_slice($ar[0],0,$limit));
				}
			} else {
				$more = FALSE;
				$text = join("",array_slice($ar[0],0));
			}
		}
		return $text . ( $more ? ' ...' : '' );
	}

	/**
	 * @brief cutHtmlStr HTML 格式字符串截取
	 *
	 * @param $content 原字符串
	 * @param $maxlen 截取长度
	 *
	 * @return string
	 */
	public static function cutHtmlStr( $content, $maxlen = 300 ) {
		// 把字符按HTML标签变成数组。
		$content = preg_split("/(<[^>]+?>)/si",$content, -1,PREG_SPLIT_NO_EMPTY| PREG_SPLIT_DELIM_CAPTURE);
		// 中英字数
		$wordrows = 0;
		// 生成的字串
		$outstr = "";
		// 是否符合最大的长度
		$wordend = false;
		// 除<img><br><hr>这些短标签外，其它计算开始标签，如<div*>
		$beginTags = 0;
		// 计算结尾标签，如</div>，如果$beginTags==$endTags表示标签数目相对称，可以退出循环。
		$endTags = 0;

		foreach($content as $value) {
			// 如果该值为空，则继续下一个值
			if (trim($value)=="") continue;
			if (strpos(";$value","<")>0) {
				// 如果与要载取的标签相同，则到处结束截取。
				if (trim($value)==$maxlen) {
					$wordend=true;
					continue;
				}
				if ($wordend==false) {
					$outstr.=$value;
					if (!preg_match("/<img([^>]+?)>/is",$value) && !preg_match("/<param([^>]+?)>/is",$value) && !preg_match("/<!([^>]+?)>/is",$value) && !preg_match("/<br([^>]+?)>/is",$value) && !preg_match("/<hr([^>]+?)>/is",$value)) {
						// 除img,br,hr外的标签都加1
						$beginTags++;
					}
				} else if (preg_match("/<\/([^>]+?)>/is",$value,$matches)) {
					$endTags++;
					$outstr.=$value;
					// 字已载完了，并且标签数相称，就可以退出循环。
					if ($beginTags==$endTags && $wordend==true) break;
				} else {
					if (!preg_match("/<img([^>]+?)>/is",$value) && !preg_match("/<param([^>]+?)>/is",$value) && !preg_match("/<!([^>]+?)>/is",$value) && !preg_match("/<br([^>]+?)>/is",$value) && !preg_match("/<hr([^>]+?)>/is",$value)) {
						// 除img,br,hr外的标签都加1
						$beginTags++;
						$outstr.=$value;
					}
				}
			}else{
				// 截取字数
				if (is_numeric($maxlen)){
					$curLength = self::getStrLen($value);
					$maxLength = $curLength + $wordrows;
					if ($wordend==false){
						// 总字数大于要截取的字数，要在该行要截取
						if ($maxLength>$maxlen){
							$outstr .= self::cutStr( $value, $maxlen-$wordrows );
							$wordend = true;
						}else{
							$wordrows = $maxLength;
							$outstr .= $value;
						}
					}
				}else{
					if ($wordend==false) $outstr .= $value;
				}
			}
		}

		// 循环替换掉多余的标签，如<p></p>这一类
		while(preg_match("/<([^\/][^>]*?)><\/([^>]+?)>/is",$outstr)){
			$outstr=preg_replace_callback("/<([^\/][^>]*?)><\/([^>]+?)>/is",array('self','stripEmptyHtml'),$outstr);
		}
		// 把误换的标签换回来
		if (strpos(";".$outstr,"[html_")>0){
			$outstr=str_replace("[html_<]","<",$outstr);
			$outstr=str_replace("[html_>]",">",$outstr);
		}
		return $outstr;
	}

	/**
	 * @brief stripEmptyHtml 去掉多余的空标签
	 *
	 * @param $matches 字符
	 *
	 * @return string
	 */
	public static function stripEmptyHtml($matches){
		$arr_tags1=explode(" ",$matches[1]);
		if ($arr_tags1[0]==$matches[2]){ //如果前后标签相同，则替换为空。
			return "";
		}else{
			$matches[0]=str_replace("<","[html_<]",$matches[0]);
			$matches[0]=str_replace(">","[html_>]",$matches[0]);
			return $matches[0];
		}
	}

	/**
	 * @brief getStrLen 取得字符串的长度，包括中英文。
	 *
	 * @param $text 字符
	 *
	 * @return int
	 */
	public static function getStrLen($text){
		if (function_exists('mb_substr')) {
			$length=mb_strlen($text,'UTF-8');
		} elseif (function_exists('iconv_substr')) {
			$length=iconv_strlen($text,'UTF-8');
		} else {
			preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);
			$length=count($ar[0]);
		}
		return $length;
	}

	/**
	 * @brief getDomain 取得顶级域名。
	 *
	 * @param $host 域名
	 *
	 * @return string
	 */
	public static function getDomain( $host ){
		$host=strtolower($host);
		if( strpos($host,'/') !== false ) {
			$parse = @parse_url($host);
			$host = $parse['host'];
		}
		$topleveldomaindb = array('com','edu','gov','net','org','biz','info','name','mobi','cc','me');
		$str = '';
		foreach( $topleveldomaindb as $v ) {
			$str .= ($str ? '|' : '').$v;
		}
		$matchstr = "[^.]+.(?:(".$str.")|w{2}|((".$str.").w{2}))$";
		if( preg_match( "/".$matchstr."/ies", $host, $matchs ) ) {
			$domain = $matchs['0'];
		}else{
			$domain = $host;
		}
		return $domain;
	}

}
