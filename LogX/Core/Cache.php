<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class Cache {

	// 缓存句柄
	private static $handle;

	/**
	 * @brief connect 连接到缓存实例
	 *
	 * @param $type 缓存类型
	 *
	 * @return void
	 */
	public static function connect( $type ) {
		$file = LOGX_CORE . 'Cache/' . ucwords( strtolower( $type ) ) . '.php';
		if( file_exists( $file ) ) {
			@include_once $file;
		} else {
			throw new LogXException( _t( 'Cache type unsupport.' ) );
		}
		$name = ucwords( strtolower( $type ) ) . 'Cache';
		self::$handle = new $name();
	}

	/**
	 * @brief set 增加或修改一个缓存变量
	 *
	 * @param $index 变量名
	 * @param $value 变量值
	 * @param $timeout 过期时间
	 *
	 * @return void
	 */
	public static function set( $index, $value = '', $timeout = 0 ) {
		return self::$handle->set( $index, $value, $timeout );
	}

	/**
	 * @brief get 获取一个缓存变量
	 *
	 * @param $index 变量名
	 *
	 * @return mix
	 */
	public static function get( $index ) {
		return self::$handle->get( $index );
	}

	/**
	 * @brief getAll 获取所有缓存
	 *
	 * @return array
	 */
	public static function getAll() {
		return self::$handle->getAll();
	}

	/**
	 * @brief refresh 刷新一条缓存
	 *
	 * @param $index 变量名
	 * @param $p 是否强制刷新
	 *
	 * @return void
	 */
	public static function refresh( $index, $p = FALSE ) {
		return self::$handle->refresh( $index, $p );
	}

	/**
	 * @brief refreshAll 刷新整个缓存
	 *
	 * @param $p 是否强制刷新
	 *
	 * @return void
	 */
	public static function refreshAll( $p = FALSE ) {
		return self::$handle->refreshAll( $p );
	}

	/**
	 * @brief delete 删除一条缓存
	 *
	 * @param $index 变量名
	 *
	 * @return void
	 */
	public static function delete($index) {
		return self::$handle->delete($index);
	}

	/**
	 * @brief deleteAll 删除整个缓存
	 *
	 * @return void
	 */
	public static function deleteAll() {
		return self::$handle->deleteAll();
	}

	/**
	 * @brief getType 获取缓存类型
	 *
	 * @return string
	 */
	public static function getType() {
		return self::$handle->getType();
	}

	/**
	 * @brief getOptions 获取配置信息
	 *
	 * @return array
	 */
	public static function getOptions() {
		return self::$handle->getOptions();
	}

}
