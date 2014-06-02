<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class Request {

	private static $_prefix = '';

	private static $_isMobile = NULL;

	public static function G( $key, $type = 'int' ) {
		return self::GPCS( isset($_GET[$key])?$_GET[$key]:NULL, $type );
	}

	public static function P( $key, $type = 'int' ) {
		return self::GPCS( isset($_POST[$key])?$_POST[$key]:NULL, $type );
	}

	public static function C( $key, $type = 'int' ) {
		if( self::$_prefix == '' ) {
			self::$_prefix = substr( md5( DB_PREFIX ), 0, 8 );
		}
		return self::GPCS( isset($_COOKIE[self::$_prefix.$key])?$_COOKIE[self::$_prefix.$key]:NULL, $type );
	}

	public static function S( $key, $type = 'int' ) {
		return self::GPCS( isset($_SERVER[$key])?$_SERVER[$key]:NULL, $type );
	}

	private static function GPCS( $value, $type ) {
		switch( $type ) {
			case 'int':
				if( $value == NULL ) {
					$value = 0;
				}
				$value = intval( $value );
				break;
			case 'array':
				if( !is_array( $value ) ) {
					$value = array( $value );
				}
				break;
		}
		return $value;
	}

	/**
	 * @brief getIP 获取访客 IP 地址
	 *
	 * @return string
	 */
	public static function getIP() {
		if( preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', self::S('HTTP_X_FORWARDED_FOR','string') ) ) {
			return self::S('HTTP_X_FORWARDED_FOR','string');
		} elseif ( preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', self::S('HTTP_CLIENT_IP','string') ) ) {
			return self::S('HTTP_CLIENT_IP','string');
		} elseif ( preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', self::S('REMOTE_ADDR','string') ) ) {
			return self::S('REMOTE_ADDR','string');
		} else {
			return 'Unknown';
		}
	}

	/**
	 * @brief isAjax 判断是否为 Ajax 请求
	 *
	 * @return bool
	 */
	public static function isAjax() {
		if( self::S('HTTP_X_REQUESTED_WITH','string') != NULL ) {
			if( 'xmlhttprequest' == strtolower( self::S('HTTP_X_REQUESTED_WITH','string') ) ) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @brief isSecure 判断是否为 HTTPS 访问
	 *
	 * @return bool
	 */
	public static function isSecure() {
		// 协议
		if (!$_SERVER['REQUEST_URI'] || ($https = @parse_url($_SERVER['REQUEST_URI']))===false) {
			$https = array();
		}
		if ((empty($https['scheme']) && ((isset($_SERVER['HTTP_SCHEME']) && $_SERVER['HTTP_SCHEME']=='https') || isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!='off')) || (isset($https['scheme']) && $https['scheme']=='https')) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief isMobile 判断是否为手机等便携式设备访问
	 *
	 * @return bool
	 */
	public static function isMobile() {
		if( self::$_isMobile === TRUE || Request::C( 'isMobile', 'string' ) == 'TRUE' ) {
			return TRUE;
		} elseif( self::$_isMobile === FALSE || Request::C( 'isMobile', 'string' ) == 'FALSE' ) {
			return FALSE;
		}
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
		self::$_isMobile = FALSE;
		foreach( $mobile_agents as $device ) {
			if( stristr( $user_agent, $device ) ) {
				self::$_isMobile = TRUE;
				break;
			}
		}
		if( self::$_isMobile ) {
			Response::setCookie( 'isMobile', 'TRUE', time()+3600*24*365 );
		} else {
			Response::setCookie( 'isMobile', 'FALSE', time()+3600*24*365 );
		}
		return self::$_isMobile;
	}

	public static function getDomain() {
		// 协议
		if( self::isSecure() ) {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}

		// 域名或IP地址  
		if( isset($_SERVER['HTTP_X_FORWARDED_HOST']) ) {
			$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
		} elseif (isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		} else {
			/* 端口 */
			if (isset($_SERVER['SERVER_PORT'])) {
				$port = ':' . $_SERVER['SERVER_PORT'];
				
				if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
					$port = '';
				}
			} else {
				$port = '';
			}

			if (isset($_SERVER['SERVER_NAME'])) {
				$host = $_SERVER['SERVER_NAME'] . $port;
			} elseif (isset($_SERVER['SERVER_ADDR'])) {
				$host = $_SERVER['SERVER_ADDR'] . $port;
			}
		}
		return $protocol . $host;
	}

}
