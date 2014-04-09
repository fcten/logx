<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class Response {

	private static $_prefix = '';

	// HTTP 状态码
	private static $_httpCode = array(
		100	=> 'Continue',
		101	=> 'Switching Protocols',
		200	=> 'OK',
		201	=> 'Created',
		202	=> 'Accepted',
		203	=> 'Non-Authoritative Information',
		204	=> 'No Content',
		205	=> 'Reset Content',
		206	=> 'Partial Content',
		300	=> 'Multiple Choices',
		301	=> 'Moved Permanently',
		302	=> 'Found',
		303	=> 'See Other',
		304	=> 'Not Modified',
		305	=> 'Use Proxy',
		307	=> 'Temporary Redirect',
		400	=> 'Bad Request',
		401	=> 'Unauthorized',
		402	=> 'Payment Required',
		403	=> 'Forbidden',
		404	=> 'Not Found',
		405	=> 'Method Not Allowed',
		406	=> 'Not Acceptable',
		407	=> 'Proxy Authentication Required',
		408	=> 'Request Timeout',
		409	=> 'Conflict',
		410	=> 'Gone',
		411	=> 'Length Required',
		412	=> 'Precondition Failed',
		413	=> 'Request Entity Too Large',
		414	=> 'Request-URI Too Long',
		415	=> 'Unsupported Media Type',
		416	=> 'Requested Range Not Satisfiable',
		417	=> 'Expectation Failed',
		500	=> 'Internal Server Error',
		501	=> 'Not Implemented',
		502	=> 'Bad Gateway',
		503	=> 'Service Unavailable',
		504	=> 'Gateway Timeout',
		505	=> 'HTTP Version Not Supported'
	);

	/**
	 * @brief setExpire 设置网页过期时间
	 *
	 * @param $m 过期时间，分钟
	 *
	 * @return void
	 */
	public static function setExpire( $m ) {
		$client_time = isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) : 0;
		$now = time();
		$now_list = time() - 60 * $m ;
		if ( $client_time < $now && $client_time > $now_list ) {
			@header('Last-Modified: '.gmdate('D, d M Y H:i:s', $client_time).' GMT', true, 304);
			exit(0);
		} else {
			@header('Last-Modified: '.gmdate('D, d M Y H:i:s', $now).' GMT', true, 200);
		}
	}

	/**
	 * @brief logo 输出 logo
	 *
	 * @return void
	 */
	public static function logo() {
		// 设置一年过期
		self::setExpire( 60 * 24 * 365 );
		@header( 'content-Type: image/gif', true );
		die(base64_decode('R0lGODlhMgASAMQAAAAAAP///1nG4GPK4m7N5HjR5oPU6I3Y6pfb7KLf7qzj8Lfm8szt9eD0+cHq89bx9+v4+/X8/QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACwAAAAAMgASAAAF/6CQDEJpnmiqqkODpK2DBM5q3+tBp0TUCBACbkhkkFIKw6xGbKqWvABEYBwaBNcrqsDtDgqo0eAoAB+4gQROEVjQAloTZA5pQB4RVeMn0C1MDmQqBAMHAyNqYAKEXV0GaikILwIDDIoFaTYKEYICM2AQDCkMeSsIDZ0KnSeHKgolBnEmSjangj0HNgmcKAYBYA+iKAx8KAiKAqomyisDk2GwyCYFzycND7CZixG5OaUnBhFCD38oC8UlQmPTiyQjNoVPJVwpBd3pEZAoEQy43A0HAAI0EfBAPnHmFihciE7Eqj5gEBWaaMjERET1GnG5R+mBNEDVnBBBUK5EpY8lFg6EFInDHsuXriRJesUyBAA7'));
	}

	/**
	 * @brief error 错误输出函数
	 *
	 * @param $errCode 错误码
	 *
	 * @return void
	 */
	public static function error( $errCode, $errMessage = '' ) {
		// 清除输出缓存
		@ob_end_clean();

		if( array_key_exists( $errCode, self::$_httpCode ) ) {
			$errMessage = self::$_httpCode[$errCode];
			@header( "HTTP/1.1 {$errCode} {$errMessage}" );
			@header( "status: {$errCode} {$errMessage}" );
		}

		if( Request::isAjax() ) {
			// 如果是 Ajax 请求
			if( defined( 'LOGX_DEBUG' ) ) {
				$r = array(
					'success' => FALSE,
					'message' => $errMessage
				);
			} else {
				$r = array(
					'success' => FALSE,
					'message' => _t('Sorry, some error occured.')
				);
			}
			self::ajaxReturn( $r );
		} else {
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml"><head>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
			echo "<title>{$errCode}</title>";
			echo '<style type="text/css">';
			echo 'body { background: #f7fbe9; font-family: "Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana; }';
			echo '#error { background: #59c6e0; width: 360px; margin: 100px auto; color: #fff; padding: 10px; -moz-border-radius: 4px; -webkit-border-radius: 4px; border-radius: 4px; }';
			echo 'h1 { padding: 10px; margin: 0; font-size: 36px; }';
			echo 'p { padding: 0 20px 20px 20px; margin: 0; font-size: 12px; }';
			echo 'img { padding: 0 0 5px 300px; }';
			echo '</style></head><body><div id="error">';
			echo "<h1>{$errCode}</h1><p>{$errMessage}</p>"; 
			echo '<img src="'.LOGX_PATH.'?591E-D5FC-8065-CD36-D3E8-E45C-DB86-9197" /></div></body></html>';
		}
		exit;
	}

	/**
	 * @brief display 调用模板，显示页面
	 *
	 * @return void
	 */
	public static function display( $path, $theme ) {
		$tpl = LOGX_THEME . $theme . '/' . $path;

		// 如果是手机访问，尝试切换为简版
		if( Request::isMobile() ) {
			$tpls = LOGX_THEME . $theme . '/simple/' . $path;
			if( file_exists( $tpls ) ) {
				$tpl = $tpls;
			}
		}

		if( file_exists( $tpl ) ) {
			@include_once $tpl;
		} else {
			throw new LogXException( sprintf( _t('Template not found: %s'), $path ), E_ERROR );
		}
	}

	/**
	 * @brief ajaxReturn 返回为 Ajax 请求优化的数据格式
	 *
	 * @param $data 数据
	 * @param $type 类型
	 *
	 * @return void
	 */
	public static function ajaxReturn( $data, $type = 'JSON' ) {
		if( strtoupper( $type ) == 'JSON' ) {
			@header("Content-Type:text/html; charset=utf-8");
			$data = json_encode( $data );
		} else {
			@header("Content-Type:text/html; charset=utf-8");
		}
		echo $data;
	}

	/**
	 * @brief redirect 重定向方法
	 *
	 * @param $location 位置
	 * @param $isPermanently 是否为永久重定向
	 *
	 * @return void
	 */
	public static function redirect( $location, $isPermanently = false ) {
		@ob_end_clean();
		if ($isPermanently) {
			@header('Location: ' . $location, false, 301);
			echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>301 Moved Permanently</title></head><body><h1>Moved Permanently</h1><p>The document has moved <a href="' . $location . '">here</a>.</p></body></html>';
		} else {
			@header('Location: ' . $location, false, 302);
			echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>302 Moved Temporarily</title></head><body><h1>Moved Temporarily</h1><p>The document has moved <a href="' . $location . '">here</a>.</p></body></html>';
		}
		exit;
	}

	/**
	 * @brief back 返回上一页
	 *
	 * @return void
	 */
	public static function back() {
		// 获取来源
		$referer = Request::S('HTTP_REFERER','string');

		if( $referer == NULL ) {
			self::redirect( LOGX_PATH );
		} else {
			self::redirect( $referer );
		}
	}

	/**
	 * @brief setCookie 设置 Cookie
	 *
	 * @param $name Cookie 名
	 * @param $value Cookie 值
	 * @param $expire Cookie 过期时间
	 *
	 * @return void
	 */
	public static function setCookie( $name, $value, $expire = 0 ) {
		$path = str_replace( ' ','%20', LOGX_PATH );
		$domainFull = Request::getDomain();
		$domain = str_replace( 'http://', '', $domainFull );
		if( $domain == $domainFull ) {
			$domain = str_replace( 'https://', '', $domainFull );
			$secure = TRUE;
		} else {
			$secure = FALSE;
		}

		if( self::$_prefix == '' ) {
			self::$_prefix = substr( md5( DB_PREFIX ), 0, 8 );
		}
		setcookie( self::$_prefix.$name, $value, $expire, $path, $domain, $secure );
	}

}
