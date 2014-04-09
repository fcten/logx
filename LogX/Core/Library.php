<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class Library {

	protected $prefix;

	/**
	 * @brief __construct 构造函数
	 *
	 * @return void
	 */
	function __construct() {
		$this->prefix = DB_PREFIX;
	}

}
