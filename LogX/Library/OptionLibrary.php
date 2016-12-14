<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class OptionLibrary extends Library {

    private static $cache = array();

	/**
	 * @brief get 获取选项值
	 *
	 * @param $name 选项名
	 * @param $cache 是否使用 cache
	 *
	 * @return mix
	 */
	public static function get( $name, $cache = TRUE ) {
	    if( !$cache || !isset( self::$cache[$name] ) ) {
	        self::$cache[$name] = Database::result("SELECT `value` FROM `".DB_PREFIX."options` WHERE `name`='{$name}'");
	    }
	    
		return self::$cache[$name];
	}

	/**
	 * @brief set 设置选项值
	 *
	 * @param $name 选项名
	 * @param $value 选项值
	 *
	 * @return void
	 */
	public static function set( $name, $value ) {
	    self::$cache[$name] = $value;
	
		return Database::query("UPDATE `".DB_PREFIX."options` SET `value`='{$value}' WHERE `name`='{$name}'");
	}

	/**
	 * @brief add 添加选项
	 *
	 * @param $name 选项名
	 * @param $value 选项值
	 * @param $global 是否为全局值
	 *
	 * @return bool
	 */
	public static function add( $name, $value, $global = 0 ) {
		if( self::get( $name, FALSE ) !== NULL ) {
			return FALSE;
		}
		return Database::query("INSERT INTO `".DB_PREFIX."options` (`bid`,`name`,`value`,`global`) VALUES (1,'{$name}','{$value}',{$global})");
	}

	/**
	 * @brief del 删除选项
	 *
	 * @param $name 选项名
	 *
	 * @return void
	 */
	public static function del( $name ) {
		return Database::query("DELETE FROM `".DB_PREFIX."options` WHERE `name`='{$name}'");
	}

}
