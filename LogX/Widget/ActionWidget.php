<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class ActionWidget extends Widget {

	/**
	 * @brief doAction 请求分发处理
	 *
	 * @param $params 传入参数
	 *
	 * @return void
	 */
	public function doAction( $params ) {
		if( method_exists( $this, $params['do'].'Do' ) ) {
			$this->{$params['do'].'Do'}();
		} else {
			Response::error(404);
		}
	}

	/**
	 * @brief login 处理用户登录
	 *
	 * @return void
	 */
	private function loginDo() {
		Widget::getWidget('User')->login();
	}

	/**
	 * @brief registerDo 处理用户注册
	 *
	 * @return void
	 */
	private function registerDo() {
		Widget::getWidget('User')->register();
	}

	/**
	 * @brief logout 处理用户退出
	 *
	 * @return void
	 */
	private function logoutDo() {
		Widget::getWidget('User')->logout();
	}

	/**
	 * @brief commentDo 发表评论
	 *
	 * @return void
	 */
	private function commentDo() {
		Widget::initWidget('Comment');
		Widget::getWidget('Comment')->postComment();
	}

	/**
	 * @brief mobileDo 进行电脑版与手机版的切换
	 *
	 * @return void
	 */
	private function mobileDo() {
		if( Request::C( 'isMobile', 'string' ) == 'TRUE' ) {
			Response::setCookie( 'isMobile', 'FALSE', time()+3600*24*365 );
		} else {
			Response::setCookie( 'isMobile', 'TRUE', time()+3600*24*365 );
		}
		Response::back();
	}
}
