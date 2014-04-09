<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class PageWidget extends Widget {

	// 页面数据
	private $pages = array();

	// 当前处理页面
	private $currentPage = 0;

	/**
	 * @brief init 初始化方法
	 *
	 * @return void
	 */
	public function init() {
		$this->currentPage = 0;
		$this->query();	
	}

	/**
	 * @brief pageNext 判断是否还有下一个页面
	 *
	 * @return bool
	 */
	public function pageNext() {
		if( $this->currentPage < count( $this->pages ) ) {
			$this->currentPage ++;
			return TRUE;
		} else {
			$this->init();
			return FALSE;
		}
	}

	/**
	 * @brief pageLink 输出页面地址
	 *
	 * @return void
	 */
	public function pageLink() {
		echo Router::patch('Page',array('alias'=>$this->pages[$this->currentPage-1]['alias']));
	}

	/**
	 * @brief pageID 输出页面 ID
	 *
	 * @param $e 是否输出
	 *
	 * @return void
	 */
	public function pageID( $e = TRUE ) {
		$pid = isset( $this->pages[$this->currentPage-1]['pid'] ) ? $this->pages[$this->currentPage-1]['pid'] : 0;
		if( $e ) {
			echo $pid;
		} else {
			return $pid;
		}
	}

	/**
	 * @brief pageTitle 输出页面标题
	 *
	 * @param $e 是否输出
	 *
	 * @return void
	 */
	public function pageTitle( $e = TRUE ) {
		$title = isset( $this->pages[$this->currentPage-1]['title'] ) ? $this->pages[$this->currentPage-1]['title'] : '';
		if( $e ) {
			echo $title;
		} else {
			return $title;
		}
	}

	/**
	 * @brief pageAlias 输出页面别名
	 *
	 * @param $e 是否输出
	 *
	 * @return void
	 */
	public function pageAlias( $e = TRUE ) {
		$alias = $this->pages[$this->currentPage-1]['alias'];
		if( $e ) {
			echo $alias;
		} else {
			return $alias;
		}
	}

	/**
	 * @brief pageCurrent 判断是否为当前页面
	 *
	 * @return bool
	 */
	public function pageCurrent() {
		if( is( 'Page', array( 'alias' => $this->pageAlias(FALSE) ) ) ) {
			return TRUE;
		} else {
			if( Plugin::call( 'pageCurrent', array( 'alias' => $this->pageAlias(FALSE) ) ) === TRUE ) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * @brief pageContent 输出页面内容
	 *
	 * @return void
	 */
	public function pageContent() {
		echo Plugin::call( 'pageContent', '' );
	}

	/**
	 * @brief query 取出页面数据
	 *
	 * @return void
	 */
	public function query() {
		$post = new PostLibrary();

		$this->pages = Plugin::call( 'pages', $post->getPages() );
	}

	/**
	 * @brief showPage 显示页面
	 *
	 * @param $params 传入参数
	 *
	 * @return void
	 */
	public function showPage( $params ) {
		Widget::initWidget('Post');
		Widget::getWidget('Post')->setAlias( $params['alias'] );

		if( !Widget::getWidget('Post')->query() ) {
			Response::error(404);
		} else {
			Widget::initWidget('Comment');
			Widget::getWidget('Comment')->setAlias( $params['alias'] );
			// TODO 由用户自定义
			Widget::getWidget('Comment')->setPerPage( 100 );
			Widget::getWidget('Comment')->query();

			// 阅读计数加一
			$post = new PostLibrary();
			$post->incView( $params['alias'], FALSE );

			// 设置标题、描述
			Widget::getWidget('Global')->title = Widget::getWidget('Post')->postTitle( 0, FALSE );
			Widget::getWidget('Global')->description = Widget::getWidget('Post')->postContent( 60, TRUE, FALSE );

			$this->display('post.php');
		}
	}

	/**
	 * @brief postPage 发布页面
	 *
	 * @return void
	 */
	public function postPage() {
		$p = array();
		$p['title'] = Request::P('title','string');
		$p['alias'] = Request::P('alias','string');
		$p['content'] = Request::P('content','string');

		if( !$p['title'] || !$p['content'] || !$p['alias'] ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Title, Content and Alias can not be null.')
			);
			Response::ajaxReturn( $r );
			return;
		}

		$p['allow_reply'] = Request::P('allowComment') ? 1 : 0;

		$user = Widget::getWidget('User')->getUser();
		$p['uid'] = $user['uid'];
		$p['top'] = 0;
		$p['type'] = 2;
		$p['status'] = 1;

		$post = new PostLibrary();
		// 检查别名是否重复
		if( $post->getPage( $p['alias'] ) ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Alias already exists.')
			);
			Response::ajaxReturn( $r );
			return;
		}
		// 写入页面
		$pid = $post->postPost( $p );

		// 处理新附件
		$meta = new MetaLibrary();
		$meta->setType( 3 );
		$meta->setPID( 1000000000 );
		$attachments = $meta->getMeta();
		foreach( $attachments as $a ) {
			$meta->movRelation( $a['mid'], 1000000000, $pid );
		}

		// 插件接口
		$p['pid'] = $pid;
		Plugin::call('postPage',$p);

		$r = array(
			'success' => TRUE,
			'message' => _t('Add page success.')
		);
		Response::ajaxReturn( $r );
	}

	/**
	 * @brief editPage 编辑页面
	 *
	 * @return void
	 */
	public function editPage() {
		$p = array();
		$p['pid'] = Request::P('pid');
		$p['title'] = Request::P('title','string');
		$p['alias'] = Request::P('alias','string');
		$p['content'] = Request::P('content','string');

		if( !$p['pid'] || !$p['title'] || !$p['content'] || !$p['alias'] ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Title, Content and Alias can not be null.')
			);
			Response::ajaxReturn( $r );
			return;
		}

		$p['allow_reply'] = Request::P('allowComment') ? 1 : 0;
		$p['top'] = 0;
		$p['status'] = 1;

		$post = new PostLibrary();
		// 检查别名是否重复
		if( ( $pid = $post->getPage( $p['alias'] ) ) && ( $pid['pid'] != $p['pid'] ) ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Alias already exists.')
			);
			Response::ajaxReturn( $r );
			return;
		}
		// 写入页面
		$post->editPost( $p );

		// 处理新附件
		$meta = new MetaLibrary();
		$meta->setType( 3 );
		$meta->setPID( 1000000000 );
		$attachments = $meta->getMeta();
		foreach( $attachments as $a ) {
			$meta->movRelation( $a['mid'], 1000000000, $p['pid'] );
		}

		$r = array(
			'success' => TRUE,
			'message' => _t('Edit page success.')
		);
		Response::ajaxReturn( $r );
	}

	/**
	 * @brief deletePage 删除一个页面
	 *
	 * @return void
	 */
	public function deletePage() {
		$pid = Request::P('pid');

		// 删除文章
		$post = new PostLibrary();
		$post->deletePost( $pid );

		// 删除 Meta 关系
		$meta = new MetaLibrary();
		$meta->setPID( $pid );
		$metas = $meta->getMeta();
		foreach( $metas as $m ) {
			if( $m['type'] == 1 || $m['type'] == 2 ) {
				$meta->delRelation( $m['mid'], $pid );
			} elseif( $m['type'] == 3 ) {
				$meta->movRelation( $m['mid'], $pid, 1000000000 );
			}
		}

		// 删除评论
		$comment = new CommentLibrary();
		$comment->deleteComments( $pid );

		$r = array('success'=>TRUE);
		Response::ajaxReturn($r);
	}

}

function page_next() {
	return Widget::getWidget('Page')->pageNext();
}

function page_link() {
	return Widget::getWidget('Page')->pageLink();
}

function page_id( $e = TRUE ) {
	return Widget::getWidget('Page')->pageID( $e );
}

function page_title( $e = TRUE ) {
	return Widget::getWidget('Page')->pageTitle( $e );
}

function page_alias( $e = TRUE ) {
	return Widget::getWidget('Page')->pageAlias( $e );
}

function page_current() {
	return Widget::getWidget('Page')->pageCurrent();
}

function page_content() {
	return Widget::getWidget('Page')->pageContent();
}
