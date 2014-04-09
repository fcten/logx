<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class Theme {

	// 所有可用的主题
	private static $_themes = array();

	/**
	 * @brief init 初始化主题控制器
	 *
	 * @return void
	 */
	public static function init() {
		self::$_themes = self::getAllThemes();
	}

	/**
	 * @brief getCurrentTheme 获取当前主题
	 *
	 * @return string
	 */
	public static function getCurrentTheme() {
		if( Request::G('theme','string') != NULL ) {
			if( in_array( Request::G('theme','string'), self::$_themes ) ) {
				return Request::G('theme','string');
			}
		}
		return OptionLibrary::get('theme');
	}

	/**
	 * @brief getDefaultTheme 获取默认主题
	 *
	 * @return string
	 */
	public static function getDefaultTheme() {
		return OptionLibrary::get('theme');
	}

	/**
	 * @brief getAllThemes 获取全部可用主题
	 *
	 * @return array
	 */
	public static function getAllThemes() {
		$themes = LogX::readDir( LOGX_THEME );
		$reArray = array();
		foreach( $themes as $key => $theme ) {
			$themeName = str_replace( LOGX_THEME, '', $theme );
			if( $themeName{0} != '.' 
				&& file_exists( $theme . '/index.php' ) 
				&& file_exists( $theme . '/post.php' ) 
		       		&& file_exists( $theme . '/page.php' ) ) {
				$reArray[] = $themeName;
			}
		}
		return $reArray;
	}

	/**
	 * @brief getInfo 获取主题信息
	 *
	 * @param $plugin 主题名称
	 *
	 * @return array
	 */
	public static function getInfo( $theme ) {
		$content = file_get_contents( LOGX_THEME . $theme . '/index.php' );
		$info = array(
			'name' => '',
			'description' => '',
			'screenshot' => '',
			'author' => '',
			'version' => '',
			'link' => '',
		);
		foreach( $info as $key => $value ) {
			preg_match( '/@'.$key.' (.+?)[\n]/', $content, $match );
			$info[$key] = $match[1];
			if( $key == 'screenshot' ) {
				$info[$key] = LOGX_PATH . str_replace( LOGX_ROOT, '', LOGX_THEME ) . $theme . '/' . $info[$key];
			}
		}
		return $info;
	}

	/**
	 * @brief setTheme 设置默认主题
	 *
	 * @param $theme 主题名称
	 *
	 * @return bool
	 */
	public static function setTheme( $theme ) {
		if( in_array( $theme, self::$_themes ) ) {
			OptionLibrary::set('theme',$theme);
			return TRUE;
		} else {
			return FALSE;
		}
	}

}
