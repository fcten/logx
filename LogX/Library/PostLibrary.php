<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class PostLibrary extends Library {

	// 每页文章数
	private $perPage = 8;

	// 当前页
	private $currentPage = 1;

	// 当前 Meta
	private $currentMeta = 0;

	// 被搜索关键词
	private $searchWord = 0;

	// 当前 Author
	private $authorID = 0;

	/**
	 * @brief setPerPage 设置每页文章数
	 *
	 * @param $num 每页文章数
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
	 * @brief setCurrentPage 设置当前页
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
	 * @brief setSearchWord 设置搜索关键词
	 *
	 * @param $word 关键词
	 *
	 * @return void
	 */
	public function setSearchWord( $word ) {
		$this->searchWord = $word;	
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
	 * @brief getPosts 获取文章数据
	 *
	 * @return array
	 */
	public function getPosts() {
		$start = ( $this->currentPage - 1 ) * $this->perPage;
		$limit = $this->perPage;

		$meta = $this->currentMeta;
		$word = $this->searchWord;
		$author = $this->authorID;

		if( $author ) {
			$author = ' AND `uid`='.$author;
		} else {
			$author = '';
		}

		if( $meta != 0 ) {
			if( is_array( $meta ) ) {
				$t = '';
				foreach( $meta as $m ) {
					$t .= "{$m},";
				}
				$t = substr( $t, 0, strlen( $t )-1 );
				$meta = "R.mid IN ( {$t} )";
			} else {
				$meta = "R.mid={$meta}";
			}

			if( $word ) {
				return Database::fetchAll("SELECT P.* FROM `{$this->prefix}posts` AS P, `{$this->prefix}posts_relation` as R WHERE P.type=1 AND P.pid=R.pid AND {$meta} AND P.title LIKE '%{$word}%' {$author} ORDER BY P.top DESC, P.ptime DESC LIMIT {$start}, {$limit}");			
			} else {
				return Database::fetchAll("SELECT P.* FROM `{$this->prefix}posts` AS P, `{$this->prefix}posts_relation` as R WHERE P.type=1 AND P.pid=R.pid AND {$meta} {$author} ORDER BY P.top DESC, P.ptime DESC LIMIT {$start}, {$limit}");			
			}
		} else {
			if( $word ) {
				return Database::fetchAll("SELECT * FROM `{$this->prefix}posts` WHERE `type`=1 AND `title` LIKE '%{$word}%' {$author} ORDER BY `top` DESC, `ptime` DESC LIMIT {$start}, {$limit}");
			} else {
				return Database::fetchAll("SELECT * FROM `{$this->prefix}posts` WHERE `type`=1 {$author} ORDER BY `top` DESC, `ptime` DESC LIMIT {$start}, {$limit}");
			}
		}
	}

	/**
	 * @brief getPost 获取一篇文章的数据
	 *
	 * @param $pid 文章 ID
	 *
	 * @return array
	 */
	public function getPost( $pid, $ispid = TRUE ) {
		if( $ispid ) {
			return Database::fetchOneArray("SELECT * FROM `{$this->prefix}posts` WHERE `type`=1 AND `pid`={$pid}");
		} else {
			return Database::fetchOneArray("SELECT * FROM `{$this->prefix}posts` WHERE `type`=1 AND `alias`='{$pid}'");
		}
	}

	/**
	 * @brief getPages 获取页面数据
	 *
	 * @return array
	 */
	public function getPages() {
		return Database::fetchAll("SELECT * FROM `{$this->prefix}posts` WHERE `type`=2 ORDER BY `top` ASC, `ptime` DESC");	
	}

	/**
	 * @brief getPage 获取一个页面的数据
	 *
	 * @param $alias 页面别名
	 *
	 * @return array
	 */
	public function getPage( $alias, $isalias = TRUE ) {
		if( $isalias ) {
			return Database::fetchOneArray("SELECT * FROM `{$this->prefix}posts` WHERE `type`=2 AND `alias`='{$alias}'");
		} else {
			return Database::fetchOneArray("SELECT * FROM `{$this->prefix}posts` WHERE `type`=2 AND `pid`={$alias}");
		}
	}

	/**
	 * @brief getPrev 获取上一篇文章
	 *
	 * @TODO 获取同分类的下一篇文章
	 *
	 * @param $pid 文章 ID
	 *
	 * @return array
	 */
	public function getPrev( $pid ) {
		return Database::fetchOneArray("SELECT * FROM `{$this->prefix}posts` WHERE `pid`<{$pid} AND `type`=1 ORDER BY `pid` DESC LIMIT 0,1");
	}

	/**
	 * @brief getNext 获取下一篇文章
	 *
	 * @TODO 获取同分类的下一篇文章
	 *
	 * @param $pid 文章 ID
	 *
	 * @return array
	 */
	public function getNext( $pid ) {
		return Database::fetchOneArray("SELECT * FROM `{$this->prefix}posts` WHERE `pid`>{$pid} AND `type`=1 ORDER BY `pid` ASC LIMIT 0,1");
	}

	/**
	 * @brief incReply 增加评论计数
	 *
	 * @param $pid 文章或者页面 ID、别名
	 *
	 * @return void
	 */
	public function incReply( $pid, $ispid = TRUE ) {
		if( $ispid ) {
			return Database::query("UPDATE `{$this->prefix}posts` SET `reply`=`reply`+1 WHERE `pid`={$pid}");
		} else {
			return Database::query("UPDATE `{$this->prefix}posts` SET `reply`=`reply`+1 WHERE `alias`='{$pid}'");
		}
	}

	/**
	 * @brief decReply 减少评论计数
	 *
	 * @param $pid 文章或者页面 ID、别名
	 *
	 * @return void
	 */
	public function decReply( $pid, $ispid = TRUE ) {
		if( $ispid ) {
			return Database::query("UPDATE `{$this->prefix}posts` SET `reply`=`reply`-1 WHERE `pid`={$pid}");
		} else {
			return Database::query("UPDATE `{$this->prefix}posts` SET `reply`=`reply`-1 WHERE `alias`='{$pid}'");
		}
	}

	/**
	 * @brief resetReply 重置评论计数
	 *
	 * @param $pid 文章或者页面 ID、别名
	 *
	 * @return void
	 */
	public function resetReply( $pid, $ispid = TRUE ) {
		if( $ispid ) {
			return Database::query("UPDATE `{$this->prefix}posts` SET `reply`=0 WHERE `pid`={$pid}");
		} else {
			return Database::query("UPDATE `{$this->prefix}posts` SET `reply`=0 WHERE `alias`='{$pid}'");
		}
	}

	/**
	 * @brief incView 增加浏览计数
	 *
	 * @param $pid 文章或者页面 ID、别名
	 *
	 * @return void
	 */
	public function incView( $pid, $ispid = TRUE ) {
		if( $ispid ) {
			return Database::query("UPDATE `{$this->prefix}posts` SET `view`=`view`+1 WHERE `pid`={$pid}");
		} else {
			return Database::query("UPDATE `{$this->prefix}posts` SET `view`=`view`+1 WHERE `alias`='{$pid}'");
		}
	}

	/**
	 * @brief nav 反回分页数据
	 *
	 * @return mix
	 */
	public function nav() {
		$author = $this->authorID;
		$currentPage = $this->currentPage;
		$currentMeta = $this->currentMeta;
		$searchWord = $this->searchWord;
		$maxPerPage = $this->perPage;

		$where = '';
		if( $author ) {
			$where .= " AND P.uid={$author}";
		}
		if( $currentMeta ) {
			$where .= " AND R.mid={$currentMeta}";
		}
		if( $searchWord ) {
			$where .= " AND P.title LIKE '%{$searchWord}%'";
		}

		$totalPost = Database::fetchAll("SELECT P.pid FROM `{$this->prefix}posts` AS P, `{$this->prefix}posts_relation` AS R WHERE P.type=1 AND P.pid=R.pid{$where} GROUP BY P.pid");
		$totalPost = count( $totalPost );

		if( $totalPost <= $maxPerPage ) {
			return FALSE;
		}
		if( ( $totalPost % $maxPerPage ) == 0 ) {
			$totalPage = floor( $totalPost / $maxPerPage );
		} else {
			$totalPage = floor( $totalPost / $maxPerPage ) + 1;
		}

		return array( 'totalPage' => $totalPage, 'currentPage' => $currentPage );
	}

	/**
	 * @brief postPost 写入一篇文章或页面
	 *
	 * @param $p 文章内容
	 *
	 * @return int
	 */
	public function postPost( $p ) {
		$time = time();
		Database::query("INSERT INTO `{$this->prefix}posts` 
			(`uid`,      `ptime`,`mtime`,`title`,       `alias`,        `content`,        `type`,      `status`,      `allow_reply`,      `top`,      `view`,`reply`) VALUES 
			({$p['uid']},{$time},{$time},'{$p['title']}','{$p['alias']}','{$p['content']}',{$p['type']},{$p['status']},{$p['allow_reply']},{$p['top']},0,     0)");
		return Database::insertID();
	}

	/**
	 * @brief editPost 编辑一篇文章或页面
	 *
	 * @param $p 文章内容
	 *
	 * @return int
	 */
	public function editPost( $p ) {
		$time = time();
		return Database::query("UPDATE `{$this->prefix}posts` SET `mtime`={$time}, `title`='{$p['title']}', `alias`='{$p['alias']}', `content`='{$p['content']}', `status`={$p['status']}, `allow_reply`={$p['allow_reply']}, `top`={$p['top']} WHERE `pid`={$p['pid']}");
	}

	/**
	 * @brief deletePost 删除一篇文章或页面
	 *
	 * @param $pid 文章 ID
	 *
	 * @return void
	 */
	public function deletePost( $pid ) {
		 return Database::query("DELETE FROM `{$this->prefix}posts` WHERE `pid`={$pid}");
	}

}
