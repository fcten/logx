<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class CommentWidget extends Widget {

	// 文章 ID
	private $pid = 0;

	// 文章别名
	private $alias = '';

	// 评论状态
	private $status = 1;

	// 每页显示评论
	private $perPage = 8;

	// 当前页
	private $currentPage = 1;

	// 储存的评论数据
	private $comments = array();

	// 当前处理评论指针
	private $currentComment = 0;

	/**
	 * @brief setPID 设置文章 ID
	 *
	 * @param $pid 文章 ID
	 *
	 * @return void
	 */
	public function setPID( $pid ) {
		$this->pid = intval( $pid );
	}

	/**
	 * @brief setAlias 设置文章别名
	 *
	 * @param $alias 文章别名
	 *
	 * @return void
	 */
	public function setAlias( $alias ) {
		$this->alias = $alias;
	}

	/**
	 * @brief setStatus 设置评论状态
	 *
	 * @param $status 评论状态
	 *
	 * @return void
	 */
	public function setStatus( $status ) {
		$this->status = intval( $status );
	}

	/**
	 * @brief setPerPage 设置每页显示评论数
	 *
	 * @param $num 每页显示评论数
	 *
	 * @return void
	 */
	public function setPerPage( $num ) {
		$this->perPage = intval( $num );
		if( $this->perPage <= 0 ) {
			$this->perPage = 8;
		}
	}

	/**
	 * @brief setCurrentPage 设置当前页数
	 *
	 * @param $page 当前页
	 *
	 * @return void
	 */
	public function setCurrentPage( $page ) {
		$this->currentPage = intval( $page );
		if( $this->currentPage <= 0 ) {
			$this->currentPage = 1;
		}
	}

	/**
	 * @brief query 根据条件取出评论数据
	 *
	 * @return void
	 */
	public function query() {
		$comment = new CommentLibrary();
		$comment->setPID( $this->pid );
		$comment->setAlias( $this->alias );
		$comment->setStatus( $this->status );
		$comment->setPerPage( $this->perPage );
		$comment->setCurrentPage( $this->currentPage );
		$this->comments = $comment->getComments();
		$this->currentComment = 0;
	}

	/**
	 * @brief commentNext 判断下一条评论是否存在
	 *
	 * @return bool
	 */
	public function commentNext() {
		if( $this->currentComment < count( $this->comments ) ) {
			$this->currentComment ++;
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief commentID 获取评论 ID
	 *
	 * @param $e 是否输出
	 *
	 * @return mix
	 */
	public function commentID( $e = TRUE ) {
		$cid = $this->comments[$this->currentComment-1]['cid'];
		if( $e ) {
			echo $cid;
		} else {
			return $cid;
		}
	}

	/**
	 * @brief commentAuthor 输出评论作者
	 *
	 * @return void
	 */
	public function commentAuthor() {
		echo $this->comments[$this->currentComment-1]['author'];
	}

	/**
	 * @brief commentAvatar 输出评论头像
	 *
	 * @param $size 头像大小
	 *
	 * @return void
	 */
	public function commentAvatar( $size = 32 ) {
		$email = $this->comments[$this->currentComment-1]['email'];
		$avatar = Plugin::call('commentAvatar', array( 'email'=>$email, 'size'=>$size ) );
		if( is_string( $avatar ) ) {
			echo $avatar;
		} else {
			echo LOGX_PATH.'?591E-D5FC-8065-CD36-D3E8-E45C-DB86-9197';
		}
	}

	/**
	 * @brief commentEmail 输出评论作者邮箱
	 *
	 * @return void
	 */
	public function commentEmail() {
		echo $this->comments[$this->currentComment-1]['email'];
	}

	/**
	 * @brief commentWebsite 输出评论作者主页
	 *
	 * @return void
	 */
	public function commentWebsite() {
		$website = $this->comments[$this->currentComment-1]['website'];
		if( $website == '' ) {
			echo '#';
		} elseif( substr( $website, 0, 7 ) == 'http://' ) {
			echo $website;
		} else {
			echo 'http://',$website;
		}
	}

	/**
	 * @brief commentDate 输出评论时间
	 *
	 * @param $format 时间格式
	 *
	 * @return void
	 */
	public function commentDate( $format = 'Y-m-d H:i:s' ) {
		echo date( $format, $this->comments[$this->currentComment-1]['ptime'] );
	}

	/**
	 * @brief commentContent 输出评论内容
	 *
	 * @return void
	 */
	public function commentContent() {
		echo $this->comments[$this->currentComment-1]['content'];
	}

	/**
	 * @brief commentParent 输出上级评论 ID
	 *
	 * @return void
	 */
	public function commentParent() {
		echo $this->comments[$this->currentComment-1]['parent'];
	}

	/**
	 * @brief commentNav 输出评论分页
	 *
	 * @param $e 是否输出
	 *
	 * @return void
	 */
	public function commentNav( $e = TRUE ) {
		$comment = new CommentLibrary();
		$comment->setStatus( $this->status );
		$comment->setPerPage( $this->perPage );
		$comment->setCurrentPage( $this->currentPage );
		$nav = $comment->nav();

		if( !is_array( $nav ) ) {
			return;
		}

		if( !$e ) {
			return $nav;
		}
	}

	/**
	 * @brief postComment 发表评论
	 *
	 * @return void
	 */
	public function postComment() {
		$c = array();
		// 如果用户已登录，则可以不填写基本信息
		if( Widget::getWidget('User')->isLogin() ) {
			$user = Widget::getWidget('User')->getUser();
			$c['uid'] = $user['uid'];
			$c['author'] = $user['username'];
			$c['email'] = $user['email'];
			$c['website'] = $user['website'];
		} else {
			$c['uid'] = 0;
			$c['author'] = Request::P('author','string');
			$c['email'] = Request::P('email','string');
			$c['website'] = Request::P('website','string');
		}
		$c['pid'] = Request::P('postId');
		$c['content'] = Request::P('content','string');

		$error = '';

		if( !$c['pid'] || !$c['author'] || !$c['email'] || !$c['content'] ) {
			// 检查信息完整性
			$error = _t('Author, Email and Content can not be null.');
		} else {
			// 检查文章是否存在、是否允许评论
			Widget::initWidget('Post');
			$post = new PostLibrary();
			$p = $post->getPost( $c['pid'] );
			if( $p ) {
				Widget::getWidget('Post')->setPID( $c['pid'] );
			} else {
				$p =$post->getPage( $c['pid'], FALSE );
				Widget::getWidget('Post')->setAlias( $p['alias'] );
			}

			if( !Widget::getWidget('Post')->query() || !Widget::getWidget('Post')->postAllowReply() ) {
				$error = _t('Comment closed.');
			} else {
				// TODO 敏感词过滤

				// TODO 内容处理
				$c['content'] = str_replace( array("\r\n","\n","\r"), '<br />', htmlspecialchars( $c['content'] ) );
				$c = Plugin::call('postComment',$c);

				// 写入评论
				$comment = new CommentLibrary();
				$comment->postComment( $c );
				// 评论计数加一
				$post->incReply( $c['pid'] );

				// 保存用户信息
				Response::setCookie( 'author', $c['author'], time() + 24 * 3600 * 365 );
				Response::setCookie( 'email', $c['email'], time() + 24 * 3600 * 365 );
				Response::setCookie( 'website', $c['website'], time() + 24 * 3600 * 365 );
			}
		}

		if( $error ) {
			$r = array(
				'success' => FALSE,
				'message' => $error
			);
		} else {
			$r = array(
				'success' => TRUE,
				'message' => _t('Post comment success.')
			);
		}
		if( Request::isAjax() ) {
			Response::ajaxReturn( $r );
		} else {
			if( $error ) {
				Response::error(_t('Post failed'),$error);
			} else {
				Response::back();
			}
		}
	}

	/**
	 * @brief deleteComment 删除一条评论
	 *
	 * @return void
	 */
	public function deleteComment() {
		$cid = Request::P('cid');

		// 删除评论
		$comment = new CommentLibrary();
		$comment->deleteComment( $cid );

		$r = array('success'=>TRUE);
		Response::ajaxReturn($r);
	}

	/**
	 * @brief censorComment 审核一条评论
	 *
	 * @return void
	 */
	public function censorComment() {
		$cid = Request::P('cid');

		// 删除评论
		$comment = new CommentLibrary();
		$comment->censorComment( $cid );

		$r = array('success'=>TRUE);
		Response::ajaxReturn($r);
	}

}

function comment_next() {
	return Widget::getWidget('Comment')->commentNext();
}

function comment_id() {
	return Widget::getWidget('Comment')->commentID();
}

function comment_author() {
	return Widget::getWidget('Comment')->commentAuthor();
}

function comment_avatar( $size = 32 ) {
	return Widget::getWidget('Comment')->commentAvatar( $size );
}

function comment_email() {
	return Widget::getWidget('Comment')->commentEmail();
}

function comment_website() {
	return Widget::getWidget('Comment')->commentWebsite();
}

function comment_date( $format = 'Y-m-d H:i:s' ) {
	return Widget::getWidget('Comment')->commentDate( $format );
}

function comment_content() {
	return Widget::getWidget('Comment')->commentContent();
}

function comment_parent() {
	return Widget::getWidget('Comment')->commentParent();
}

function comment_nav( $e = TRUE ) {
	return Widget::getWidget('Comment')->commentNav( $e );
}
