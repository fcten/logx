<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class Plugin {

	// 插件对象池
	private static $_plugins;

	// 全部钩子
	private static $_hooks;

	// 当前使用的主题
	protected $theme = '';

	// 数据库前缀
	protected $prefix = '';

	/**
	 * @brief __construct 构造函数
	 *
	 * @return void
	 */
	function __construct() {
		$this->theme = Theme::getCurrentTheme();
		$this->prefix = DB_PREFIX;	
	}

	/**
	 * @brief __set 魔术方法，用于注册钩子
	 *
	 * @param $name 钩子名
	 * @param $value 挂钩函数
	 *
	 * @return void
	 */
	public function __set( $name, $value ) {
		return Plugin::set( $name, $value );
	}

	/**
	 * @brief __get 魔术方法，用于获取注册的钩子
	 *
	 * @param $name 钩子名
	 *
	 * @return array
	 */
	public function __get( $name ) {
		return Plugin::get( $name );
	}

	/**
	 * @brief __call 魔术方法，用于处理钩子
	 *
	 * @param $name 钩子名
	 * @param $arguments 钩子参数
	 *
	 * @return mix
	 */
	public function __call( $name, $arguments ) {
		return Plugin::call( $name, $arguments );
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
	 * @brief set 注册钩子
	 *
	 * @param $name 钩子名
	 * @param $value 挂钩函数
	 *
	 * @return void
	 */
	public static function set( $name, $value ) {
		if( isset( self::$_hooks[$name] ) ) {
			self::$_hooks[$name][] = $value;
		} else {
			self::$_hooks[$name] = array( $value );
		}
	}

	/**
	 * @brief get 获取钩子
	 *
	 * @param $name 钩子名
	 *
	 * @return array
	 */
	public static function get( $name ) {
		return self::$_hooks[$name];
	}

	/**
	 * @brief call 处理挂钩调用
	 *
	 * @param $name 钩子名称
	 * @param $arguments 参数
	 *
	 * @return void
	 */
	public static function call( $name, $arguments ) {
		if( !isset( self::$_hooks[$name] ) ) {
			return $arguments;
		}
		foreach( self::$_hooks[$name] as $hook ) {
			$arguments = self::getPlugin($hook[0])->{$hook[1]}( $arguments );
		}
		return $arguments;
	}

	/**
	 * @brief init 插件初始化方法
	 *
	 * @return void
	 */
	public function init() {
	}

	/**
	 * @brief install 插件安装方法
	 *
	 * @return bool
	 */
	public function install() {
		return TRUE;
	}

	/**
	 * @brief remove 插件卸载方法
	 *
	 * @return bool
	 */
	public function remove() {
		return TRUE;
	}

	/**
	 * @brief config 显示插件配置界面
	 *
	 * @return void
	 */
	public function config() {
		$r = array(
			'success' => FALSE,
			'message' => _t('No settings')
		);
		Response::ajaxReturn( $r );
	}

	/**
	 * @brief getPlugins 获取所有可用的插件
	 *
	 * @return array
	 */
	public static function getPlugins() {
		$plugins = LogX::readDir( LOGX_PLUGIN );
		$reArray = array();
		foreach( $plugins as $plugin ) {
			$pluginName = str_replace( LOGX_PLUGIN, '', $plugin );
			if( $pluginName{0} != '.'  && file_exists( $plugin . '/' . $pluginName . '.php' ) ) {
				$reArray[] = $pluginName;
			}
		}
		return $reArray;
	}

	/**
	 * @brief initPlugins 初始化所有激活的插件，用于让注册插件钩子
	 *
	 * @return void
	 */
	public static function initPlugins() {
		$plugins = self::getPlugins();
		foreach( $plugins as $plugin ) {
			$pluginPath = LOGX_PLUGIN . $plugin . '/';
			if( self::isInstall( $plugin ) && @require_once $pluginPath . $plugin . '.php' ) {
				$pluginName = $plugin . 'Plugin';
				if( class_exists( $pluginName ) ) {
					self::$_plugins[$plugin] = new $pluginName;
					if( method_exists( $pluginName, 'init' ) ) {
						self::$_plugins[$plugin]->init();
					}
				}
			}
		}
	}

	/**
	 * @brief getPlugin 获取插件对象
	 *
	 * @param $plugin 插件名称
	 *
	 * @return object
	 */
	public static function getPlugin( $plugin ) {
		if( isset( self::$_plugins[$plugin] ) ) {
			return self::$_plugins[$plugin];
		} else {
			throw new LogXException( sprintf( _t('Try to use unknow plugin: %s') , $plugin ), E_ERROR );
		}
	}

	/**
	 * @brief getInfo 获取插件信息
	 *
	 * @param $plugin 插件名称
	 *
	 * @return array
	 */
	public static function getInfo( $plugin ) {
		$content = file_get_contents( LOGX_PLUGIN . $plugin . '/' . $plugin . '.php' );
		$info = array(
			'name' => '',
			'description' => '',
			'author' => '',
			'version' => '',
			'link' => '',
		);
		foreach( $info as $key => $value ) {
			preg_match( '/@'.$key.' (.+?)[\n]/', $content, $match );
			$info[$key] = $match[1];
		}
		return $info;
	}

	/**
	 * @brief isInstall 检查插件是否安装
	 *
	 * @param $plugin 插件名称
	 *
	 * @return bool
	 */
	public static function isInstall( $plugin ) {
		$pluginInstall = Cache::get( 'PluginInstall' );
		if( isset( $pluginInstall[$plugin] ) ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief enable 安装插件
	 *
	 * @param $plugin 插件名称
	 *
	 * @return void
	 */
	public static function enable( $plugin ) {
		// 检查是否已经安装
		if( self::isInstall( $plugin ) ) {
			Response::ajaxReturn( array( 'success'=>FALSE, 'message'=>_t('Already installed.') ) );
			return;
		}

		// 调用插件自身的 install 方法
		require_once LOGX_PLUGIN . $plugin . '/' . $plugin . '.php';
		$pluginName = $plugin.'Plugin';
		if( !class_exists( $pluginName ) ) {
			Response::ajaxReturn( array( 'success'=>FALSE, 'message'=>_t('Plugin broken.') ) );
			return;
		}
		$po = new $pluginName;
		if( !$po->install() ) {
			Response::ajaxReturn( array( 'success'=>FALSE, 'message'=>_t('Install failed. Maybe this plugin cannot run on current version of LogX.') ) );
			return;
		}

		// 标记插件为已安装
		$pluginInstall = Cache::get( 'PluginInstall' );
		$pluginInstall[$plugin] = TRUE;
		Cache::set( 'PluginInstall', $pluginInstall, 0 );

		Response::ajaxReturn( array( 'success'=>TRUE, 'message'=>_t('Installation complete.') ) );
	}

	/**
	 * @brief disable 卸载插件
	 *
	 * @param $plugin 插件名称
	 *
	 * @return void
	 */
	public static function disable( $plugin ) {
		// 检查是否已经安装
		if( !self::isInstall( $plugin ) ) {
			Response::ajaxReturn( array( 'success'=>FALSE, 'message'=>_t('Already removed.') ) );
			return;
		}

		// 调用插件自身的 remove 方法
		require_once LOGX_PLUGIN . $plugin . '/' . $plugin . '.php';
		$pluginName = $plugin.'Plugin';
		if( !class_exists( $pluginName ) ) {
			Response::ajaxReturn( array( 'success'=>FALSE, 'message'=>_t('Plugin broken.') ) );
			return;
		}
		$po = new $pluginName;
		if( !$po->remove() ) {
			Response::ajaxReturn( array( 'success'=>FALSE, 'message'=>_t('Remove failed.') ) );
			return;
		}

		// 标记插件为已卸载
		$pluginInstall = Cache::get( 'PluginInstall' );
		unset( $pluginInstall[$plugin] );
		Cache::set( 'PluginInstall', $pluginInstall, 0 );

		Response::ajaxReturn( array( 'success'=>TRUE, 'message'=>_t('Remove complete.') ) );
	}

}

function plugin_call( $name, $arguments = NULL ) {
	return Plugin::call( $name, $arguments );
}
