<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class CommentLibrary extends Library {

	// 文章 ID
	private $pid;

	// 文章别名
	private $alias;

	// 评论状态
	private $status = 1;

	// 每页文章数
	private $perPage = 8;

	// 当前页
	private $currentPage = 1;

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
	 * @brief setPerPage 设置每页评论数
	 *
	 * @param $num 每页评论数
	 *
	 * @return void
	 */
	public function setPerPage( $num ) {
		$this->perPage = $num;	
	}

	/**
	 * @brief setCurrentPage 设置当前页
	 *
	 * @param $page 当前页
	 *
	 * @return void
	 */
	public function setCurrentPage( $page ) {
		$this->currentPage = $page;
	}

	/**
	 * @brief getComments 获取评论数据
	 *
	 * @return array
	 */
	public function getComments() {
		$start = ( $this->currentPage - 1 ) * $this->perPage;
		$limit = $this->perPage;

		if( !$this->pid && !$this->alias ) {
			return Database::fetchAll("SELECT * FROM `{$this->prefix}comments` WHERE `status`={$this->status} ORDER BY `ptime` ASC LIMIT {$start}, {$limit}");
		}
		if( $this->alias ) {
			$post = new PostLibrary();
			$p = $post->getPage( $this->alias );
			$this->pid = $p['pid'];
		}
		return Database::fetchAll("SELECT * FROM `{$this->prefix}comments` WHERE `pid`={$this->pid} AND `status`={$this->status} ORDER BY `ptime` ASC LIMIT {$start}, {$limit}");
	}

	/**
	 * @brief nav 反回分页数据
	 *
	 * @return mix
	 */
	public function nav() {
		$currentPage = $this->currentPage;
		$maxPerPage = $this->perPage;

		if( !$this->pid && !$this->alias ) {
			$totalComment = Database::result("SELECT COUNT(`cid`) FROM `{$this->prefix}comments` WHERE `status`={$this->status}");
		} else{ 
			if( $this->alias ) {
				$post = new PostLibrary();
				$p = $post->getPage( $this->alias );
				$this->pid = $p['pid'];
			}
			$totalComment = Database::result("SELECT COUNT(`cid`) FROM `{$this->prefix}comments` WHERE `pid`={$this->pid} AND `status`={$this->status}");
		}

		if( $totalComment <= $maxPerPage ) {
			return FALSE;
		}
		if( ( $totalComment % $maxPerPage ) == 0 ) {
			$totalPage = floor( $totalComment / $maxPerPage );
		} else {
			$totalPage = floor( $totalComment / $maxPerPage ) + 1;
		}

		return array( 'totalPage' => $totalPage, 'currentPage' => $currentPage );
	}

	/**
	 * @brief postComment 写入一条评论
	 *
	 * @param $c 评论信息
	 *
	 * @return int
	 */
	public function postComment( $c ) {
		$time = time();
		$ip = Request::getIP();
		Database::query("INSERT INTO `{$this->prefix}comments` 
			(`pid`,      `uid`,      `author`,        `email`,        `website`,        `content`,        `status`,       `ptime`,`mtime`,`ip`,   `parent`) VALUES 
			({$c['pid']},{$c['uid']},'{$c['author']}','{$c['email']}','{$c['website']}','{$c['content']}',{$this->status},{$time},{$time},'{$ip}',0)");
		return Database::insertID();
	}

	/**
	 * @brief deleteComment 删除一条评论
	 *
	 * @param $cid 评论 ID
	 *
	 * @return void
	 */
	public function deleteComment( $cid ) {
		$post = new PostLibrary();
		$pid = Database::result("SELECT `pid` FROM `{$this->prefix}comments` WHERE `cid`={$cid}");
		if( $pid ) {
			$post->decReply( $pid );
			return Database::query("DELETE FROM `{$this->prefix}comments` WHERE `cid`={$cid}");
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief censorComment 审核一条评论
	 *
	 * @param $cid 评论 ID
	 *
	 * @return void
	 */
	public function censorComment( $cid ) {
		$post = new PostLibrary();
		$pid = Database::result("SELECT `pid` FROM `{$this->prefix}comments` WHERE `cid`={$cid}");
		if( $pid ) {
			return Database::query("UPDATE `{$this->prefix}comments` SET `status`=1 WHERE `cid`={$cid}");
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief deleteComments 删除某文章的所有评论
	 *
	 * @param $pid 文章 ID
	 *
	 * @return void
	 */
	public function deleteComments( $pid ) {
		$post = new PostLibrary();
		$post->resetReply( $pid );
		return Database::query("DELETE FROM `{$this->prefix}comments` WHERE `pid`={$pid}");
	}

}
