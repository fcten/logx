<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class AdminWidget extends Widget {

	// 记录所请求的操作
	private $action;

	/**
	 * @brief init 初始化 Admin 组件
	 *
	 * @return void
	 */
	public function init() {
		$this->theme = 'admin';
	}

	/**
	 * @brief showAdmin 显示后台管理页面
	 *
	 * @return void
	 */
	public function showAdmin() {
		// 验证用户权限
		if( Widget::getWidget('User')->isLogin() ) {
			if( Widget::getWidget('User')->isEditor() ) {
				$this->display('index.php');
			} else {
				Response::redirect( Router::patch('Index','') );
			}
		} else {
			$this->display('login.php');
		}
	}

	/**
	 * @brief doAdmin 处理后台请求
	 *
	 * @param $params 传入参数
	 *
	 * @return void
	 */
	public function doAdmin( $params ) {
		// 验证用户权限
		if( !Widget::getWidget('User')->isEditor() ) {
			return;
		}
		// 分发请求
		if( method_exists( $this, $params['do'].'Do' ) ) {
			if( !Request::isAjax() && $params['do'] != 'upload' ) {
				$this->display('head.php');
			}

			$this->action = $params['do'];
			$this->{$params['do'].'Do'}();

			if( !Request::isAjax() && $params['do'] != 'upload' ) {
				$this->display('foot.php');
			}
		} else {
			Response::error(404);
		}
	}

	/**
	 * @brief getAction 获取所请求的操作
	 *
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * @brief terminalDo 显示控制台模块
	 *
	 * @return void
	 */
	private function terminalDo() {
		$this->display('terminal/index.php');
	}

	/**
	 * @brief contentManagePostsDo 管理内容
	 *
	 * @return void
	 */
	private function contentManagePostsDo() {
		$this->display('content/Posts.php');
	}

	/**
	 * @brief contentManagePagesDo 管理内容
	 *
	 * @return void
	 */
	private function contentManagePagesDo() {
		$this->display('content/Pages.php');
	}

	/**
	 * @brief contentManageCommentsDo 管理内容
	 *
	 * @return void
	 */
	private function contentManageCommentsDo() {
		$this->display('content/Comments.php');
	}

	/**
	 * @brief contentManageCategoriesDo 管理内容
	 *
	 * @return void
	 */
	private function contentManageCategoriesDo() {
		$this->display('content/Categories.php');
	}

	/**
	 * @brief contentManageTagsDo 管理内容
	 *
	 * @return void
	 */
	private function contentManageTagsDo() {
		$this->display('content/Tags.php');
	}

	/**
	 * @brief addPostDo 添加文章
	 *
	 * @return void
	 */
	private function addPostDo() {
		// 非管理员只能添加文章到到自己有权限的分类下
		if( !Widget::getWidget('User')->isAdmin() ) {
			$metas = Request::P('category','array');
			foreach( $metas as $m ) {
				if( !Widget::getWidget('User')->checkPrivilege( 'POST', intval( $m ) ) ) {
					Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
					return;
				}
			}
		}

		Widget::initWidget('Post');
		Widget::getWidget('Post')->postPost();
	}

	/**
	 * @brief editPostDo 编辑文章
	 *
	 * @return void
	 */
	private function editPostDo() {
		// 验证用户权限
		// 非管理员只能编辑自己的文章
		// 如果原文章属于多个分类，那么编辑者必须拥有所有从属分类的权限
		// 如果原文章不属于任何一个分类（正常情况下不会出现），那么任何人均可以编辑该文章
		if( !Widget::getWidget('User')->isAdmin() ) {
			$pid = Request::P('pid');
			$meta = new MetaLibrary();
			$meta->setPID( $pid );
			$meta->setType( 1 );
			$metas = $meta->getMeta( FALSE );
			foreach( $metas as $m ) {
				if( !Widget::getWidget('User')->checkPrivilege( 'POST', $m['mid'] ) ) {
					Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
					return;
				}
			}
		}

		Widget::initWidget('Post');
		Widget::getWidget('Post')->editPost();
	}

	/**
	 * @brief delPostDo 删除文章
	 *
	 * @return void
	 */
	private function delPostDo() {
		// 验证用户权限
		// 只有管理员可以删除文章
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		Widget::initWidget('Post');
		Widget::getWidget('Post')->deletePost();
	}

	/**
	 * @brief addPageDo 添加页面
	 *
	 * @return void
	 */
	private function addPageDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		Widget::initWidget('Page');
		Widget::getWidget('Page')->postPage();
	}

	/**
	 * @brief editPageDo 编辑页面
	 *
	 * @return void
	 */
	private function editPageDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		Widget::initWidget('Page');
		Widget::getWidget('Page')->editPage();
	}

	/**
	 * @brief delPageDo 删除页面
	 *
	 * @return void
	 */
	private function delPageDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		Widget::initWidget('Page');
		Widget::getWidget('Page')->deletePage();
	}

	/**
	 * @brief delCommentDo 删除评论
	 *
	 * @return void
	 */
	private function delCommentDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		Widget::initWidget('Comment');
		Widget::getWidget('Comment')->deleteComment();
	}

	/**
	 * @brief censorCommentDo 审核评论
	 *
	 * @return void
	 */
	private function censorCommentDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		Widget::initWidget('Comment');
		Widget::getWidget('Comment')->censorComment();
	}

	/**
	 * @brief addCategoryDo 添加分类
	 *
	 * @return void
	 */
	private function addCategoryDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		Widget::initWidget('Meta');
		Widget::getWidget('Meta')->addMeta( 1 );
	}

	/**
	 * @brief editCategoryDo 编辑分类
	 *
	 * @return void
	 */
	private function editCategoryDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		Widget::initWidget('Meta');
		Widget::getWidget('Meta')->editMeta( 1 );
	}

	/**
	 * @brief addTagDo 添加标签
	 *
	 * @return void
	 */
	private function addTagDo() {
		Widget::initWidget('Meta');
		Widget::getWidget('Meta')->addMeta( 2 );
	}

	/**
	 * @brief editTagDo 编辑标签
	 *
	 * @return void
	 */
	private function editTagDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		Widget::initWidget('Meta');
		Widget::getWidget('Meta')->editMeta( 2 );
	}

	/**
	 * @brief delMetaDo 删除 Meta
	 *
	 * @return void
	 */
	private function delMetaDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		Widget::initWidget('Meta');
		Widget::getWidget('Meta')->delMeta();
	}

	/**
	 * @brief uploadDo 上传文件
	 *
	 * @return void
	 */
	private function uploadDo() {
		$upload = new UploadLibrary('jpg|png|jpeg|gif|bmp|zip|rar|gz|tar', '2048', 'Filedata');
		$upload->set_dir( LOGX_FILE, '{y}{m}' );
		// $upload->set_watermark( LOGX_THEME.'admin/images/watermark.gif', 6, 50 );
		// $upload->set_thumb( 150, 150 );
		// $upload->set_resize(500, 500);

		$files = $upload->execute();

		$r = array('success'=>FALSE);
		if (!$files) { 
			$r['message'] = _t('您没有选择任何文件，或者文件大小超出 post_max_size 限定！');
		} else {
			if ( $files['status'] == 1 ) {
				$r['success'] = TRUE;
				$r['message'] = '<li class="multiline" id="attach-'.$files['mid'].'"><label for="attach-'.$files['mid'].'">'.$files['ogname'].'</label><a href="#" onclick="insertToEditor('."'".Router::patch( 'Attachment', array( 'mid'=>$files['mid'] ) )."','".$files['type']."','".$files['ogname']."'".');return false;">['._t('Insert').']</a>&nbsp;&nbsp;<a href="#" onclick="deleteAttachment('.$files['mid'].');return false;">['._t('Delete').']</a></li>';
			} elseif ( $files['status'] == -1 ) {
				$r['message'] = _t('文件类型不允许！');
			} elseif ( $files['status'] == -2 ) {
				$r['message'] = _t('文件大小超出程序限定的 2M！');
			} elseif ( $files['status'] == -3 ) {
				$r['message'] = _t('文件大小超出 upload_max_filesize 限定！');
			} else {
				$r['message'] = _t('未知错误！');
			}
		}
		Response::ajaxReturn($r);
	}

	/**
	 * @brief themeDo 显示主题管理模块
	 *
	 * @return void
	 */
	private function themeDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( _t('Permission denied.') );
			return;
		}

		$this->display('theme/index.php');
	}

	/**
	 * @brief setThemeDo 设置主题
	 *
	 * @return void
	 */
	private function setThemeDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		$theme = Request::P('theme','string');
		if( Theme::setTheme( $theme ) ) {
			Response::ajaxReturn( array('success'=>TRUE,'message'=>_t('Theme has been changed.')) );
		} else {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Change theme failed.')) );
		}
	}

	/**
	 * @brief pluginDo 显示插件管理模块
	 *
	 * @return void
	 */
	private function pluginDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( _t('Permission denied.') );
			return;
		}

		$this->display('plugin/index.php');
	}

	/**
	 * @brief pluginInstallDo 安装插件
	 *
	 * @return void
	 */
	private function pluginInstallDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		$plugin = Request::P('plugin','string');
		Plugin::enable( $plugin );
	}

	/**
	 * @brief pluginRemoveDo 移除插件
	 *
	 * @return void
	 */
	private function pluginRemoveDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		$plugin = Request::P('plugin','string');
		Plugin::disable( $plugin );
	}

	/**
	 * @brief pluginSettingDo 设置插件
	 *
	 * @return void
	 */
	private function pluginSettingDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		$plugin = Request::P('plugin','string');
		Plugin::getPlugin( $plugin )->config();
	}

	/**
	 * @brief userDo 显示用户管理模块
	 *
	 * @return void
	 */
	private function userDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( _t('Permission denied.') );
			return;
		}

		$this->display('user/index.php');
	}

	/**
	 * @brief editUserDo 编辑用户
	 *
	 * @return void
	 */
	private function editUserDo() {
		// 验证用户权限
		// TODO 允许非管理员编辑自己
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		Widget::getWidget('User')->editUser();
	}

	/**
	 * @brief systemDo 显示系统管理模块
	 *
	 * @return void
	 */
	private function systemDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( _t('Permission denied.') );
			return;
		}

		$this->display('system/index.php');
	}

	/**
	 * @brief basicSettingsDo 保存基本设置
	 *
	 * @return void
	 */
	private function basicSettingsDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		$name = Request::P('name','string');
		$keywords = Request::P('keywords','string');
		$description = Request::P('description','string');
		$domain = Request::P('domain','string');
		if( !$name || !$keywords || !$description || !$domain ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Option can not be null.')
			);
		} else {
			OptionLibrary::set('title',$name);
			OptionLibrary::set('keywords',$keywords);
			OptionLibrary::set('description',$description);
			OptionLibrary::set('domain',$domain);
			$r = array(
				'success' => TRUE,
				'message' => _t('Settings Saved.')
			);
		}
		Response::ajaxReturn( $r );
	}

	/**
	 * @brief advancedSettingsDo 保存高级设置
	 *
	 * @return void
	 */
	private function advancedSettingsDo() {
		// 验证用户权限
		if( !Widget::getWidget('User')->isAdmin() ) {
			Response::ajaxReturn( array('success'=>FALSE,'message'=>_t('Permission denied.')) );
			return;
		}

		$rewrite = Request::P('rewrite','string');
		$timezone = Request::P('timezone','string');
		$register = Request::P('register','string');
		if( !$rewrite || !$timezone || !$register ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Option can not be null.')
			);
			Response::ajaxReturn( $r );
		} else {
			if( $rewrite == 'close' ) {
				if( file_exists( LOGX_ROOT . '.htaccess' ) && !@unlink( LOGX_ROOT . '.htaccess' ) ) {
					$r = array(
						'success' => FALSE,
						'message' => _t('Can not delete .htaccess file.')
					);
					Response::ajaxReturn( $r );
					return;
				}
			} else {
				$content = "# BEGIN LogX\n\n<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase ".LOGX_PATH."\nRewriteCond $1 ^(index\.php)?$ [OR]\nRewriteCond $1 \.(gif|jpg|png|css|js|ico)$ [NC,OR]\nRewriteCond %{REQUEST_FILENAME} -f [OR]\nRewriteCond %{REQUEST_FILENAME} -d\nRewriteRule ^(.*)$ - [S=1]\nRewriteRule . ".LOGX_PATH."index.php [L]\n</IfModule>\n\n# END LogX";
				if( !file_exists( LOGX_ROOT . '.htaccess' ) && !@file_put_contents( LOGX_ROOT . '.htaccess', $content ) ) {
					$r = array(
						'success' => FALSE,
						'message' => _t('Can not create .htaccess file.')
					);
					Response::ajaxReturn( $r );
					return;
				}
			}
			OptionLibrary::set('rewrite',$rewrite);
			OptionLibrary::set('timezone',$timezone);
			OptionLibrary::set('register',$register);
			$r = array(
				'success' => TRUE,
				'message' => _t('Settings Saved.')
			);
			Response::ajaxReturn( $r );
		}
	}

}

function admin_action() {
	return Widget::getWidget('Admin')->getAction();
}
