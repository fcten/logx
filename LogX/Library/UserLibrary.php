<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class UserLibrary extends Library {

	// 用户 ID
	private $uid = 0;

	// 用户名
	private $name = '';

	// 每页数据条数
	private $perPage = 8;

	// 当前页数
	private $currentPage = 1;

	/**
	 * @brief setUID 设置用户 ID
	 *
	 * @param $uid 用户 ID
	 *
	 * @return void
	 */
	public function setUID( $uid ) {
		$this->uid = intval( $uid );
	}

	/**
	 * @brief setName 设置用户名
	 *
	 * @param $name 用户名
	 *
	 * @return void
	 */
	public function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * @brief setPerPage 设置每页条数
	 *
	 * @param $num 每页条数
	 *
	 * @return void
	 */
	public function setPerPage( $num ) {
		$this->perPage = $num;
	}

	/**
	 * @brief setCurrentPage 设置当前页数
	 *
	 * @param $num 当前页数
	 *
	 * @return void
	 */
	public function setCurrentPage( $num ) {
		$this->currentPage = $num;
	}

	/**
	 * @brief getUser 获取用户数据
	 *
	 * @return array
	 */
	public function getUser() {
		if( !$this->uid && !$this->name ) {
			return FALSE;
		}

		$where = '';
		if( $this->uid ) {
			$where .= " AND `uid`={$this->uid}";
		}
		if( $this->name ) {
			$where .= " AND `username`='{$this->name}'";
		}

		return Database::fetchOneArray("SELECT * FROM `{$this->prefix}users` WHERE 1=1{$where}");
	}

	/**
	 * @brief editUser 编辑用户数据
	 *
	 * @param $u 用户数据
	 *
	 * @return void
	 */
	public function editUser( $u ) {
		if( $u['password'] ) {
			$this->setUID( $u['uid'] );
			$this->updatePassword( $u['password'] );
		}
		return Database::query("UPDATE `{$this->prefix}users` SET `email`='{$u['email']}', `website`='{$u['website']}' WHERE `uid`={$u['uid']}");
	}

	/**
	 * @brief addUser 添加用户
	 *
	 * @param $u 用户数据
	 *
	 * @return int
	 */
	public function addUser( $u ) {
		// 检查是否重复
		if( Database::result("SELECT `uid` FROM `{$this->prefix}users` WHERE `username`='{$u['username']}'") ) {
			return 0;
		}
		if( Database::result("SELECT `uid` FROM `{$this->prefix}users` WHERE `email`='{$u['email']}'") ) {
			return 0;
		}

		$time = time();
		Database::query("INSERT INTO `{$this->prefix}users` 
			(`username`,        `password`,`group`,      `email`,        `website`,        `rtime`,`auth`) VALUES 
			('{$u['username']}','',        {$u['group']},'{$u['email']}','{$u['website']}',{$time},'LogX')");
		$u['uid'] = Database::insertId();
		if( $u['password'] ) {
			$this->setUID( $u['uid'] );
			$this->updatePassword( $u['password'] );
		}
		return $u['uid'];
	}

	/**
	 * @brief getUsers 分页获取用户数据
	 *
	 * @return array
	 */
	public function getUsers() {
		$start = ( $this->currentPage - 1 ) * $this->perPage;
		$limit = $this->perPage;

		return Database::fetchAll("SELECT * FROM `{$this->prefix}users` LIMIT {$start}, {$limit}");
	}

	/**
	 * @brief nav 分页数据
	 *
	 * @return array
	 */
	public function nav() {
		$currentPage = $this->currentPage;
		$maxPerPage = $this->perPage;

		$totalUsers = Database::result("SELECT COUNT(`uid`) FROM `{$this->prefix}users`");

		if( $totalUsers <= $maxPerPage ) {
			return FALSE;
		}
		if( ( $totalUsers % $maxPerPage ) == 0 ) {
			$totalPage = floor( $totalUsers / $maxPerPage );
		} else {
			$totalPage = floor( $totalUsers / $maxPerPage ) + 1;
		}

		return array( 'totalPage' => $totalPage, 'currentPage' => $currentPage );
	}

	/**
	 * @brief updatePassword 更新密码
	 *
	 * @param $pw 密码
	 *
	 * @return void
	 */
	public function updatePassword( $pw ) {
		if( !$this->uid && !$this->name ) {
			return FALSE;
		}

		$pw = md5( $pw );

		$where = '';
		if( $this->uid ) {
			$where .= " AND `uid`={$this->uid}";
		}
		if( $this->name ) {
			$where .= " AND `username`='{$this->name}'";
		}

		return Database::query("UPDATE `{$this->prefix}users` SET `password`='{$pw}' WHERE 1=1{$where}");
	}

	/**
	 * @brief updateSalt 更新安全键
	 *
	 * @param $salt 随机字符串
	 *
	 * @return mix
	 */
	public function updateSalt( $salt ) {
		if( !$this->uid && !$this->name ) {
			return FALSE;
		}

		$where = '';
		if( $this->uid ) {
			$where .= " AND `uid`={$this->uid}";
		}
		if( $this->name ) {
			$where .= " AND `username`='{$this->name}'";
		}

		return Database::query("UPDATE `{$this->prefix}users` SET `auth`='{$salt}' WHERE 1=1{$where}");
	}

	/**
	 * @brief checkPrivilege 检查用户权限
	 *
	 * @param $name 权限项目名称 (VIEW, POST, DELETE, COMMENT)
	 * @param $category 分类 ID
	 *
	 * @return bool
	 */
	public function checkPrivilege( $name, $category ) {
		if( !$this->uid ) {
			return FALSE;
		}

		$where = '';
		if( $this->uid ) {
			$where .= " AND `uid`={$this->uid}";
		}

		if( !( $p = Database::result("SELECT `value` FROM `{$this->prefix}users_meta` WHERE name='privilege'{$where}") ) ) {
			return FALSE;
		}

		$p = unserialize( $p );
		if( !is_array( $p ) ) {
			return FALSE;
		}

		foreach( $p as $v ) {
			if( $v == $category ) {
				return TRUE;
			}
		}
		return FALSE;
	}

}
