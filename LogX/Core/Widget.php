<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class Widget {

	// 组件对象池
	private static $_widgets;

	// 当前使用的主题
	protected $theme = '';

	/**
	 * @brief __construct 构造函数
	 *
	 * @return void
	 */
	function __construct() {
		$this->theme = Theme::getCurrentTheme();	
	}

	/**
	 * @brief init 组件初始化方法，由子类继承
	 *
	 * @return void
	 */
	public function init() {
	}

	/**
	 * @brief display 调用模板，显示页面
	 *
	 * @return void
	 */
	public function display( $path ) {
		Response::display( $path, $this->theme );
	}

	/**
	 * @brief getWidgets 获取可用的组件
	 *
	 * @return array
	 */
	public static function getWidgets() {
		$widgets = Logx::readFile( LOGX_WIDGET, 'php' );
		$reArray = array();
		foreach( $widgets as $widget ) {
			if( substr( $widget, -10 ) == 'Widget.php' ) {
				$widgetName = str_replace( array( LOGX_WIDGET, 'Widget.php' ), array( '', '' ), $widget );
				$reArray[] = $widgetName;
			}
		}
		return $reArray;
	}

	/**
	 * @brief initWidget 初始化一个组件
	 *
	 * @param $widgetName 组件名
	 *
	 * @return bool
	 */
	public static function initWidget( $widgetName ) {
		if( isset( self::$_widgets[$widgetName] ) ) {
			return TRUE;
		}
		if( is_file( LOGX_WIDGET . $widgetName . 'Widget.php' ) ) {
			@include_once LOGX_WIDGET . $widgetName . 'Widget.php';
			$fullWidgetName = $widgetName . 'Widget';
			if( !class_exists( $fullWidgetName ) ) {
				throw new LogXException( sprintf( _t('Illegal widget: %s.'), $widgetName ) );
			}
			self::$_widgets[$widgetName] = new $fullWidgetName;
			self::$_widgets[$widgetName]->init();
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief getWidget 获取一个可供访问的组件实例
	 *
	 * @param $widgetName 组件名
	 *
	 * @return object
	 */
	public static function getWidget( $widgetName ) {
		if( isset( self::$_widgets[$widgetName] ) ) {
			return self::$_widgets[$widgetName];
		} else {
			if( self::initWidget( $widgetName ) ) {
				return self::$_widgets[$widgetName];
			} else {
				throw new LogXException( sprintf( _t('Illegal widget: %s.'), $widgetName ) );
			}
		}
	}

}
