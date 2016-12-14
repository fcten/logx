<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class PostWidget extends Widget {

	// 每页显示文章
	private $perPage = 8;

	// 当前页
	private $currentPage = 1;

	// 当前 Meta
	private $currentMeta = 0;

	// 被搜索关键词
	private $searchWord = 0;

	// 文章数据
	private $posts = array();

	// 当前被处理的文章指针
	private $currentPost = 0;
	
	// 被处理文章 ID
	private $pid = 0;

	// 被处理文章别名
	private $alias = '';

	// 当前作者
	private $authorID = 0;

	/**
	 * @brief setPerPage 设置每页显示文章数
	 *
	 * @param $num 每页显示文章数
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
	 * @brief setCurrentMeta 设置当前 Meta
	 *
	 * @param $meta 当前 Meta
	 *
	 * @return void
	 */
	public function setCurrentMeta( $meta ) {
		if( is_array( $meta ) ) {
			foreach( $meta as $k => $v ) {
				$meta[$k] = intval( $meta[$k] );
			}
			$this->currentMeta = $meta;
		} else {
			$this->currentMeta = intval( $meta );
		}
	}

	/**
	 * @brief setSearchWord 设置搜索词
	 *
	 * @param $word 关键词
	 *
	 * @return void
	 */
	public function setSearchWord( $word ) {
		$this->searchWord = $word;
	}

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
	 * @brief setAuthor 设置文章作者
	 *
	 * @param $uid 用户ID
	 *
	 * @return void
	 */
	public function setAuthor( $uid ) {
		$this->authorID = $uid;
	}

	/**
	 * @brief query 根据条件取出文章数据
	 *
	 * @return bool
	 */
	public function query() {
		$post = new PostLibrary();
		if( $this->pid ) {
			$this->currentPost = 1;
			$page = $post->getPost( $this->pid );
			if( $page ) {
				$this->posts = array( $page );
				return TRUE;
			} else {
				return FALSE;
			}
		} elseif( $this->alias ) {
			$this->currentPost = 1;
			$page = $post->getPage( $this->alias );
			if( $page ) {
				$this->posts = array( $page );
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			$this->currentPost = 0;
			$post->setPerPage( $this->perPage );
			$post->setAuthor( $this->authorID );
			$post->setCurrentPage( $this->currentPage );
			$post->setCurrentMeta( $this->currentMeta );
			$post->setSearchWord( $this->searchWord );

			$this->posts = $post->getPosts();

			return TRUE;
		}
	}

	/**
	 * @brief postQuery 根据条件重新查询
	 *
	 * @param $meta Meta ID
	 * @param $perPage 文章数量
	 *
	 * @return void
	 */
	public function postQuery( $meta = 0, $perPage = 0 ) {
		if( $meta ) {
			$this->setCurrentMeta( $meta );
		}
		if( $perPage ) {
			$this->setPerPage( $perPage );
		}

		$this->setPID( 0 );
		$this->setAlias( 0 );
		$this->setSearchWord( 0 );
		$this->setCurrentPage( 1 );

		$this->query();
	}

	/**
	 * @brief postHave 判断是否有文章
	 *
	 * @return bool
	 */
	public function postHave() {
		if( count( $this->posts ) ) {
			if( isset( $this->posts[$this->currentPost-1] ) ) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief postNext 移动到下一篇文章
	 *
	 * @return bool
	 */
	public function postNext() {
		if( $this->currentPost < count( $this->posts ) ) {
			$this->currentPost ++;
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief postID 输出文章 ID
	 *
	 * @param $e 是否直接输出
	 *
	 * @return mix
	 */
	public function postID( $e = TRUE ) {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		$pid = $this->posts[$this->currentPost-1]['pid'];
		if( $e ) {
			echo $pid;
		} else {
			return $pid;
		}
	}

	/**
	 * @brief postTitle 输出文章标题
	 *
	 * @param $summary 摘要字数
	 * @param $e 是否输出
	 *
	 * @return void
	 */
	public function postTitle( $summary = 0, $e = TRUE ) {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		$title = isset( $this->posts[$this->currentPost-1]['title'] ) ? $this->posts[$this->currentPost-1]['title']:'';
		if( intval( $summary ) ) {
			$title = LogX::cutStr( str_replace( array("\r\r","\r","\n"), '', strip_tags( $title ) ), intval( $summary ) );
		}
		if( $e ) {
			echo $title;
		} else {
			return $title;
		}
	}

	/**
	 * @brief postLink 输出文章链接地址
	 *
	 * @return void
	 */
	public function postLink() {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		if( $this->posts[$this->currentPost-1]['type'] == 1 ) {
			echo Router::patch( 'Post', array('pid'=>$this->posts[$this->currentPost-1]['pid']) );
		} else {
			echo Router::patch( 'Page', array('alias'=>$this->posts[$this->currentPost-1]['alias']) );
		}
	}

	/**
	 * @brief postAuthor 输出文章作者
	 *
	 * @return void
	 */
	public function postAuthor() {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		$author = $this->posts[$this->currentPost-1]['uid'];
		$user = new UserLibrary();
		$user->setUID( $author );
		$u = $user->getUser();
		echo '<a href="'.Router::patch( 'Author', array('uid'=>$author) ).'">'.$u['username'].'</a>';
	}

	/**
	 * @brief postDate 输出文章发布时间
	 *
	 * @param $format 输出时间格式
	 * @param $today 是否高亮当日日期
	 *
	 * @return void
	 */
	public function postDate( $format = "Y-m-d H:i:s", $today = FALSE ) {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		$time = $this->posts[$this->currentPost-1]['ptime'];
		if( $today ) {
			$newtime = date( "Y-j-n", $time );
			list( $year, $month, $day ) = explode( "-", $newtime );
			$nowtime = date( "Y-j-n" );
			list( $nyear, $nmonth, $nday ) = explode( "-", $nowtime );
			if( $year == $nyear && $month == $nmonth && $day == $nday ) {
				echo '<font color="'.$today.'">'.date( $format, $time ).'</font>';
			} else {
				echo date( $format, $time );
			}
		} else {
			echo date( $format, $time );
		}
	}

	/**
	 * @brief postCategory 输出文章分类
	 *
	 * @param $mid 假如给出这个参数，函数的功能变为判断文章是否属于此分类
	 *
	 * @return mix
	 */
	public function postCategory( $mid = 0 ) {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		$meta = new MetaLibrary();
		$meta->setType( 1 );
		$meta->setPID( $this->postID( FALSE ) );
		$metas = $meta->getMeta( FALSE );

		if( $mid ) {
			foreach( $metas as $m ) {
				if( $mid == $m['mid'] ) {
					return TRUE;
				}
			}
			return FALSE;
		} else {
			$me = '';
			foreach( $metas as $m ) {
				$me .= '<a href="' . Router::patch('Category',array('alias'=>$m['alias'])) . '">' . $m['name'] . '</a> , ';
			}
			$me = substr( $me, 0 ,strlen($me)-3 );
			echo $me;
		}
	}

	/**
	 * @brief postView 输出文章阅读数
	 *
	 * @param $format 输出格式
	 *
	 * @return void
	 */
	public function postView( $format = '%d views' ) {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		echo sprintf( $format, $this->posts[$this->currentPost-1]['view'] );
	}

	/**
	 * @brief postComment 输出文章评论数
	 *
	 * @return void
	 */
	public function postComment( $formatNone = 'No Comments', $formatOne = '1 Comment', $formatAll = '%d Comments' ) {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		switch( $this->posts[$this->currentPost-1]['reply'] ) {
		case 0:
			echo $formatNone;
			break;
		case 1:
			echo $formatOne;
			break;
		default:
			echo sprintf( $formatAll, $this->posts[$this->currentPost-1]['reply'] );
		}
	}

	/**
	 * @brief postContent 输出文章内容
	 *
	 * @param $summary 摘要字数
	 * @param $noHtml 是否过滤 HTML 标签
	 * @param $e 是否输出
	 *
	 * @return mix
	 */
	public function postContent( $summary = 0, $noHtml = FALSE, $e = TRUE ) {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		if( intval( $summary ) ) {
			if( $noHtml ) {
				$r = LogX::cutStr( str_replace( array("\r\r","\r","\n"), '', strip_tags( $this->posts[$this->currentPost-1]['content'] ) ), intval( $summary ) );
			} else {
				$r = LogX::cutHtmlStr( $this->posts[$this->currentPost-1]['content'], intval( $summary ) );
			}
		} else {
			$r = $this->posts[$this->currentPost-1]['content'];
		}
		if( $e ) {
			echo $r;
		} else {
			return $r;
		}
	}

	/**
	 * @brief postThumb 输出文章封面
	 *
	 * @return void
	 */
	public function postThumb() {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		$pid = $this->posts[$this->currentPost-1]['pid'];
		$meta = new MetaLibrary();
		$meta->setType(3);
		$meta->setPID($pid);
		$attachments = $meta->getMeta();

		$flag = FALSE;
		foreach( $attachments as $a ) {
			if( strstr( $a['description'], 'image' ) !== FALSE ) {
				$flag = TRUE;
				break;
			}
		}

		if( $flag ) {
			return Router::patch('Attachment',array('mid'=>$a['mid']));
		} else {
			return '';
		}
	}

	/**
	 * @brief postTags 输出文章标签
	 *
	 * @param $e 是否输出
	 *
	 * @return void
	 */
	public function postTags( $e = TRUE ) {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		$meta = new MetaLibrary();
		$meta->setType( 2 );
		$meta->setPID( $this->postID( FALSE ) );
		$metas = $meta->getMeta();
		$me = '';
		foreach( $metas as $m ) {
			$me .= '<a href="' . Router::patch('Tag',array('name'=>urlencode($m['name']))) . '">' . $m['name'] . '</a> , ';
		}
		$me = substr( $me, 0 ,strlen($me)-3 );
		if( $e ) {
			echo $me;
		} else {
			return $me;
		}
	}

	/**
	 * @brief postPrevPost 输出上一篇文章
	 *
	 * @return void
	 */
	public function postPrevPost() {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		$post = new PostLibrary();
		$p = $post->getPrev( $this->postID( FALSE ) );
		if( $p ) {
			echo '<a href="' . Router::patch('Post',array('pid'=>$p['pid'])) . '">' . $p['title'] . '</a>';
		} else {
			echo _t('No prev posts.');
		}
	}

	/**
	 * @brief postNextPost 输出下一篇文章
	 *
	 * @return void
	 */
	public function postNextPost() {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		$post = new PostLibrary();
		$p = $post->getNext( $this->postID( FALSE ) );
		if( $p ) {
			echo '<a href="' . Router::patch('Post',array('pid'=>$p['pid'])) . '">' . $p['title'] . '</a>';
		} else {
			echo _t('No next posts.');
		}
	}

	/**
	 * @brief postNav 输出文章分页
	 *
	 * @param $e 是否输出
	 *
	 * @return void
	 */
	public function postNav( $e = TRUE ) {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		$post = new PostLibrary();
		$post->setPerPage( $this->perPage );
		$post->setAuthor( $this->authorID );
		$post->setCurrentPage( $this->currentPage );
		$post->setCurrentMeta( $this->currentMeta );
		$post->setSearchWord( $this->searchWord );
		$nav = $post->nav();

		if( !is_array( $nav ) ) {
			return;
		}

		if( !$e ) {
			return $nav;
		}

		if( $this->currentMeta != 0 ) {
			$meta = new MetaLibrary();
			$meta->setMID( $this->currentMeta );
			$m = $meta->getMeta();
			switch( $m[0]['type'] ) {
			case 1:
				$route = 'CategoryPage';
				$r = 'alias';
				$alias = $m[0]['alias'];
				break;
			case 2:
				$route = 'TagPage';
				$r = 'name';
				$alias = $m[0]['name'];
				break;
			}
		} elseif( $this->searchWord ) {
			$route = 'SearchPage';
			$r = 'word';
			$alias = $this->searchWord;
		} elseif( $this->authorID ) {
			$route = 'AuthorPage';
			$r = 'uid';
			$alias = $this->authorID;
		} else {
			$route = 'IndexPage';
			$r = NULL;
			$alias = NULL;
		}

		echo '<ol class="page-nav">';
		if( $nav['totalPage'] <= 10 ) {
			for( $i = 1; $i <= $nav['totalPage'] ; $i ++ ) {
				if( $i == $nav['currentPage'] ) {
					echo '<li class="current"><a href="'.Router::patch($route,array('page'=>$i,$r=>$alias)).'">'.$i.'</a></li>';
				} else {
					echo '<li><a href="'.Router::patch($route,array('page'=>$i,$r=>$alias)).'">'.$i.'</a></li>';
				}
			}
		} else {
			echo '<li><a href="'.Router::patch($route,array('page'=>1,$r=>$alias)).'">&laquo; '._t('First Page').'</a></li>';
			if( $nav['currentPage'] > 5  ) {
				echo '<li>...</li>';
				for( $i = $nav['currentPage'] - 4 ; $i < $nav['currentPage'] ; $i ++ ){
					echo '<li><a href="'.Router::patch($route,array('page'=>$i,$r=>$alias)).'">'.$i.'</a></li>';
				}
			} else {
				for( $i = 1 ; $i < $nav['currentPage'] ; $i ++ ){
					echo '<li><a href="'.Router::patch($route,array('page'=>$i,$r=>$alias)).'">'.$i.'</a></li>';
				}
			}
			echo '<li class="current"><a href="'.Router::patch($route,array('page'=>$nav['currentPage'],$r=>$alias)).'">'.$nav['currentPage'].'</a></li>';
			if( $nav['totalPage'] - $nav['currentPage'] > 5  ) {
				for( $i = $nav['currentPage'] + 1 ; $i < $nav['currentPage'] + 5 ; $i ++ ){
					echo '<li><a href="'.Router::patch($route,array('page'=>$i,$r=>$alias)).'">'.$i.'</a></li>';
				}
				echo '<li>...</li>';
			} else {
				for( $i = $nav['currentPage'] + 1 ; $i <= $nav['totalPage'] ; $i ++ ){
					echo '<li><a href="'.Router::patch($route,array('page'=>$i,$r=>$alias)).'">'.$i.'</a></li>';
				}
			}
			echo '<li><a href="'.Router::patch($route,array('page'=>$nav['totalPage'],$r=>$alias)).'">'._t('Last Page').' &raquo;</a></li>';
		}
		echo '</ol>';
	}

	/**
	 * @brief postAllowReply 判断是否允许评论
	 *
	 * @return bool
	 */
	public function postAllowReply() {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		if( $this->posts[$this->currentPost-1]['allow_reply'] == 1 ) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @brief postIsSticky 判断是否为置顶文章
	 *
	 * @return bool
	 */
	public function postIsSticky() {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		return $this->posts[$this->currentPost-1]['top'];
	}

	/**
	 * @brief postPath 输出文章路径
	 *
	 * @return void
	 */
	public function postPath() {
		// 检查是否有文章
		if( !$this->postHave() ) {
			return;
		}

		$path = $this->postTitle(0,FALSE);

		$meta = new MetaLibrary();
		$meta->setType( 1 );
		$meta->setPID( $this->postID( FALSE ) );
		$metas = $meta->getMeta();
		$me = isset( $metas[0]['mid'] ) ? $metas[0]['mid'] : 0;
		$m = isset( $metas[0] ) ? $metas[0] : array();
		$meta->setPID( 0 );

		while( $me ) {
			$path = '<a href="' . Router::patch('Category',array('alias'=>$m['alias'])) . '">' . $m['name'] . '</a> &raquo; '.$path;

			if( $m['parent'] == 0 ) {
				break;
			}

			$meta->setMID( $m['parent'] );
			$metas = $meta->getMeta();
			$me = isset( $metas[0]['mid'] ) ? $metas[0]['mid'] : 0;
			$m = isset( $metas[0] ) ? $metas[0] : array();
		}

		$path = '<a href="'.LOGX_PATH.'">'.OptionLibrary::get('title').'</a> &raquo; ' . $path;

		echo $path;
	}

	/**
	 * @brief showPost 显示文章页面
	 *
	 * @param $params 传入参数
	 *
	 * @return void
	 */
	public function showPost( $params ) {
		$this->setPID( $params['pid'] );
		if( $params['pid'] == 0 || !$this->query() ) {
			Response::error(404);
		} else {
			Widget::initWidget('Comment');
			Widget::getWidget('Comment')->setPID( $params['pid'] );
			// TODO 由用户自定义
			Widget::getWidget('Comment')->setPerPage( 100 );
			Widget::getWidget('Comment')->query();

			// 阅读计数加一
			$post = new PostLibrary();
			$post->incView( $params['pid'] );

			// 设置标题、关键词、描述
			Widget::getWidget('Global')->title = $this->postTitle( 0, FALSE );
			Widget::getWidget('Global')->description = $this->postContent( 60, TRUE, FALSE );
			Widget::getWidget('Global')->keywords = strip_tags( str_replace( ' , ',',',$this->postTags( FALSE ) ) );
	
			$this->display('post.php');
		}
	}

	/**
	 * @brief postPost 添加一篇文章
	 *
	 * @return void
	 */
	public function postPost() {
		$p = array();
		$p['title'] = htmlspecialchars( Request::P('title','string') );
		$p['content'] = Request::P('content','string');
		$p['category'] = Request::P('category','array');

		if( !$p['title'] || !$p['content'] || ( count( $p['category'] ) == 1 && !$p['category'][0] ) ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Title, Content and Category can not be null.')
			);
			Response::ajaxReturn( $r );
			return;
		}

		$p['allow_reply'] = Request::P('allowComment') ? 1 : 0;
		$p['top'] = Request::P('top') ? 1 : 0;

		$user = Widget::getWidget('User')->getUser();
		$p['uid'] = $user['uid'];
		$p['alias'] = '';
		$p['type'] = 1;
		$p['status'] = 1;

		// 发布文章
		$post = new PostLibrary();
		$meta = new MetaLibrary();
		$pid = $post->postPost( $p );
		// 处理分类
		foreach( $p['category'] as $c ) {
			$meta->addRelation( $c, $pid );
		}

		// 处理标签
		if( $p['tags'] = Request::P('tags','string') ) {
			$p['tags'] = str_replace( array(' ','，','、'), ',', $p['tags'] );
			$p['tags'] = explode( ',', $p['tags'] );
			$meta->setType( 2 );
			foreach( $p['tags'] as $tag ) {
				$meta->setName( $tag );
				$t = $meta->getMeta();
				if( !$t ) {
					$t = $meta->addMeta( array( 'type'=>2, 'name'=>$tag ) );
				} else {
					$t = $t[0]['mid'];
				}
				$meta->addRelation( $t, $pid );
			}
		}

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
		Plugin::call('postPost',$p);

		$r = array(
			'success' => TRUE,
			'message' => _t('Add post success.')
		);
		Response::ajaxReturn( $r );
	}

	/**
	 * @brief editPost 编辑一篇文章
	 *
	 * @return void
	 */
	public function editPost() {
		$p = array();
		$p['pid'] = Request::P('pid');
		$p['title'] = htmlspecialchars( Request::P('title','string') );
		$p['content'] = Request::P('content','string');
		$p['category'] = Request::P('category','array');

		if( !$p['pid'] || !$p['title'] || !$p['content'] || ( count( $p['category'] ) == 1 && !$p['category'][0] ) ) {
			$r = array(
				'success' => FALSE,
				'message' => _t('Title, Content and Category can not be null.')
			);
			Response::ajaxReturn( $r );
			return;
		}

		$p['allow_reply'] = Request::P('allowComment') ? 1 : 0;
		$p['top'] = Request::P('top') ? 1 : 0;
		$p['alias'] = '';
		$p['status'] = 1;

		// 编辑文章
		$post = new PostLibrary();
		$meta = new MetaLibrary();
		$post->editPost( $p );

		// 删除原有的分类与标签
		$meta->setPID( $p['pid'] );
		$metas = $meta->getMeta( FALSE );
		foreach( $metas as $m ) {
			if( $m['type'] == 1 || $m['type'] == 2 ) {
				$meta->delRelation( $m['mid'], $p['pid'] );
			}
		}
		$meta->setPID( 0 );

		// 处理分类
		foreach( $p['category'] as $c ) {
			$meta->addRelation( $c, $p['pid'] );
		}

		// 处理标签
		if( $p['tags'] = Request::P('tags','string') ) {
			$p['tags'] = str_replace( array(' ','，','、'), ',', $p['tags'] );
			$p['tags'] = explode( ',', $p['tags'] );
			$meta->setType( 2 );
			foreach( $p['tags'] as $tag ) {
				$meta->setName( $tag );
				$t = $meta->getMeta();
				if( !$t ) {
					$t = $meta->addMeta( array( 'type'=>2, 'name'=>$tag ) );
				} else {
					$t = $t[0]['mid'];
				}
				$meta->addRelation( $t, $p['pid'] );
			}
		}

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
			'message' => _t('Edit post success.')
		);
		Response::ajaxReturn( $r );
	}

	/**
	 * @brief deletePost 删除一篇文章
	 *
	 * @return void
	 */
	public function deletePost() {
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

function post_query( $meta = 0, $perPage = 0 ) {
	return Widget::getWidget('Post')->postQuery( $meta, $perPage );
}

function post_have() {
	return Widget::getWidget('Post')->postHave();
}

function post_next() {
	return Widget::getWidget('Post')->postNext();
}

function post_id( $e = TRUE ) {
	return Widget::getWidget('Post')->postID( $e );
}

function post_title( $summary = 0, $e = TRUE ) {
	return Widget::getWidget('Post')->postTitle( $summary, $e );
}

function post_link() {
	return Widget::getWidget('Post')->postLink();
}

function post_author() {
	return Widget::getWidget('Post')->postAuthor();
}

function post_date( $format = "Y-m-d H:i:s", $today = FALSE ) {
	return Widget::getWidget('Post')->postDate( $format, $today );
}

function post_category( $mid = 0 ) {
	return Widget::getWidget('Post')->postCategory( $mid );
}

function post_view( $format = '%d views' ) {
	return Widget::getWidget('Post')->postView( $format );
}

function post_comment( $formatNone = 'No Comments', $formatOne = '1 Comment', $formatAll = '%d Comments' ) {
	return Widget::getWidget('Post')->postComment( $formatNone, $formatOne, $formatAll );
}

function post_content( $summary = 0, $noHtml = FALSE, $e = TRUE ) {
	return Widget::getWidget('Post')->postContent( $summary, $noHtml, $e );
}

function post_thumb() {
	return Widget::getWidget('Post')->postThumb();
}

function post_tags() {
	return Widget::getWidget('Post')->postTags();
}

function post_prev_post() {
	return Widget::getWidget('Post')->postPrevPost();
}

function post_next_post() {
	return Widget::getWidget('Post')->postNextPost();
}

function post_nav( $e = TRUE ) {
	return Widget::getWidget('Post')->postNav( $e );
}

function post_allow_reply() {
	return Widget::getWidget('Post')->postAllowReply();
}

function post_path() {
	return Widget::getWidget('Post')->postPath();
}

function post_is_sticky() {
	return Widget::getWidget('Post')->postIsSticky();
}
