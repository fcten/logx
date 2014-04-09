<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class IndexWidget extends Widget {

	/**
	 * @brief showIndex 显示主页
	 *
	 * @return void
	 */
	public function showIndex( $params ) {
		Widget::initWidget('Post');
		Widget::getWidget('Post')->setPerPage( 8 );
		Widget::getWidget('Post')->setCurrentPage( isset($params['page'])?$params['page']:1 );
		Widget::getWidget('Post')->query();

		$this->display('index.php');
	}

}
