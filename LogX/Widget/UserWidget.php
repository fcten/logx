<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class UserWidget extends Widget {

	// 用户组权限
	private $privilege;

	// 当前用户信息
	private $user;

	/**
	 * @brief init 初始化用户组件
	 *
	 * @return void
	 */
	public function init() {
		// 初始化默认用户组权限
		$this->privilege = array(
			// 游客
			0 => array(
				'VIEW' => 'ALL',
				'POST' => 'NONE',
				'DELETE' => 'NONE',
				'COMMENT' => 'ALL'
			),
			// 普通用户
			1 => array(
				'VIEW' => 'ALL',
				'POST' => 'NONE',
				'DELETE' => 'NONE',
				'COMMENT' => 'ALL'
			),
			// 编辑
			5 => array(
				'VIEW' => 'ALL',
				'POST' => 'NONE',
				'DELETE' => 'NONE',
				'COMMENT' => 'ALL'
			),
			// 管理员
			10 => array(
				'VIEW' => 'ALL',
				'POST' => 'ALL',
				'DELETE' => 'ALL',
				'COMMENT' => 'ALL'
			)
		);

		// 初始化为游客
		$this->user = array(
			// 用户 ID
			'uid'      => 0,
			// 用户名
			'username' => '',
			// 邮箱
			'email'    => '',
			// 主页
			'website'  => '',
			// 用户组
			'group'    => 0
		);	
	}

	/**
	 * @brief getUser 获取用户信息
	 *
	 * @param $uid 用户 ID
	 *
	 * @return array
	 */
	public function getUser( $uid = 0 ) {
		if( $uid == 0 || $uid == $this->user['uid'] ) {
			return $this->user;
		} else {
			$user = new UserLibrary();
			$user->setUID( $uid );
			return $user->getUser();
		}
	}

	/**
	 * @brief editUser 编辑用户信息
	 *
	 * @return array
	 */
	public function editUser() {
		$u = array();
		$u['uid'] = Request::P( 'uid' );
		$u['password'] = Request::P( 'password', 'string' );
		$u['email'] = Request::P( 'email', 'string' );
		$u['website'] = Request::P( 'website', 'string' );

		$user = new UserLibrary();
		$user->editUser( $u );
		$r = array(
			'success' => TRUE,
			'message' => _t('Edit user complete.')
		);
		Response::ajaxReturn( $r );
	}

	/**
	 * @brief isLogin 判断当前用户是否登录
	 *
	 * @return bool
	 */
	public function isLogin() {
		if( $this->user['group'] > 0 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief isEditor 判断当前用户是否为编辑
	 *
	 * @return bool
	 */
	public function isEditor() {
		if( $this->user['group'] > 4 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief isAdmin 判断当前用户是否为管理员
	 *
	 * @return bool
	 */
	public function isAdmin() {
		if( $this->user['group'] > 9 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief checkPrivilege 检查权限
	 *
	 * @param $name 权限项目名称 (VIEW,POST,DELETE,COMMENT)
	 * @param $category 分类 ID
	 *
	 * @return bool
	 */
	public function checkPrivilege( $name, $category ) {
		if( !in_array( $name, array( 'VIEW', 'POST', 'DELETE', 'COMMENT' ) ) ) {
			return FALSE;
		}

		// 优先根据用户 ID 检查
		$user = new UserLibrary();
		$user->setUID( $this->user['uid'] );
		if( $user->checkPrivilege( $name, $category ) ) {
			return TRUE;
		}
		
		// 检查用户组权限
		if( $this->privilege[$this->user['group']][$name] == 'ALL' ) {
			return TRUE;
		}
		if( is_array( $this->privilege[$this->user['group']][$name] ) && in_array( $category, $this->privilege[$this->user['group']][$name] ) ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @brief getName 输出用户名
	 *
	 * @param $uid 用户 ID
	 * @param $e 是否输出
	 *
	 * @return void
	 */
	public function getName( $uid = 0, $e = TRUE ) {
		$user = $this->getUser( $uid );
		if( $e ) {
			echo $user['username'];
		} else {
			return $user['username'];
		}
	}

	/**
	 * @brief register 用户注册
	 *
	 * @return void
	 */
	public function register() {
		if( OptionLibrary::get('register') == 'close' ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Register closed.')
				);
			Response::ajaxReturn($r);
			return;
		}

		$u = array();
		$u['username'] = Request::P('username','string');
		$u['email'] = Request::P('email','string');

		if( $u['username'] == NULL || $u['email'] == NULL ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Username or Email missed.')
				);
			Response::ajaxReturn($r);
			return;
		}

		$u['password'] = LogX::randomString( 8 );
		$u['website'] = '';
		$u['group'] = 1;

		$user = new UserLibrary();
		if( $uid = $user->addUser( $u ) ) {
			$r = array(
				'success' => TRUE,
				'message' => sprintf( _t('Register successed, you password is <b>%s</b>.'), $u['password'] )
				);
		} else {
			$r = array(
				'success' => FALSE,
				'message' => _t('Username or Email existed.')
				);
		}
		Response::ajaxReturn($r);
	}

	/**
	 * @brief login 用户登录
	 *
	 * @return void
	 */
	public function login() {
		$username = Request::P('username','string');
		$password = Request::P('password','string');
		$remember = Request::P('remember');

		if( $username == NULL || $password == NULL ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Username or password missed.')
				);
			Response::ajaxReturn($r);
			return;
		}

		$user = new UserLibrary();
		$user->setName( $username );

		if( !( $u = $user->getUser() ) ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Username not exists.')
				);
			Response::ajaxReturn($r);
		} else {
			if( $u['password'] != strtolower( md5( $password ) ) ) {
				$r = array(
					'success' => FALSE,
					'message' => _t('Password wrong.')
					);
				Response::ajaxReturn($r);
				return;
			}

			$this->user['uid'] = $u['uid'];
			$this->user['username'] = $u['username'];
			$this->user['group'] = $u['group'];
			$this->user['email'] = $u['email'];
			$this->user['website'] = $u['website'];

			$expire = $remember ? time() + $remember : 0;

			if( $remember ) {
				$u['auth'] = LogX::randomString( 8 );
				$user->updateSalt( $u['auth'] );
			}

			Response::setCookie( 'userid', $u['uid'], $expire );
			Response::setCookie( 'password', md5( $u['auth'] . $u['password'] ), $expire );

			$r = array(
				'success' => TRUE,
				'message' => _t('Login success.')
				);
			Response::ajaxReturn($r);
		}
	}

	/**
	 * @brief logout 用户退出
	 *
	 * @return void
	 */
	public function logout() {
		Response::setCookie( 'userid', NULL, 0 );
		Response::setCookie( 'password', NULL, 0 );

		Response::back();
	}

	/**
	 * @brief autoLogin 自动登录
	 *
	 * @return void
	 */
	public function autoLogin() {
		$uid = Request::C('userid') ? Request::C('userid') : Request::P('userid');
		$password = Request::C('password','string') ? Request::C('password','string') : Request::P('password','string');

		if( $uid == NULL || $password == NULL ) {
			return;
		}

		$user = new UserLibrary();
		$user->setUID( $uid );

		if( $u = $user->getUser() ) {
			if( md5( $u['auth'] . $u['password'] ) != $password ) {
				return;
			}

			$this->user['uid'] = $u['uid'];
			$this->user['username'] = $u['username'];
			$this->user['group'] = $u['group'];
			$this->user['email'] = $u['email'];
			$this->user['website'] = $u['website'];
		}
	}

	/**
	 * @brief showUser 显示某用户发布的文章
	 *
	 * @param $params 传入参数
	 *
	 * @return void
	 */
	public function showUser( $params ) {
		// 根据 uid 获取 用户信息
		$user = new UserLibrary();
		$user->setUID( $params['uid'] );
		if( !( $u = $user->getUser() ) ) {
			Response::error( 404 );
			return;
		}

		// 获取文章数据
		Widget::initWidget('Post');
		Widget::getWidget('Post')->setPerPage( 8 );
		Widget::getWidget('Post')->setCurrentPage( isset($params['page'])?$params['page']:1 );
		Widget::getWidget('Post')->setAuthor( $u['uid'] );
		Widget::getWidget('Post')->query();

		// 设置标题、描述、关键词
		Widget::getWidget('Global')->title = urldecode( $u['username'] );

		$this->display('index.php');	
	}
}

function user_info( $uid = 0 ) {
	return Widget::getWidget('User')->getUser( $uid );
}

function user_name( $uid = 0, $e = TRUE ) {
	return Widget::getWidget('User')->getName( $uid, $e );
}

function user_is_login() {
	return Widget::getWidget('User')->isLogin();
}

function user_is_editor() {
	return Widget::getWidget('User')->isEditor();
}

function user_is_admin() {
	return Widget::getWidget('User')->isAdmin();
}
