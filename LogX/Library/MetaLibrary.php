<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class MetaLibrary extends Library {

	// Meta 种类
	private $type = 0;

	// Meta 别名
	private $alias = '';

	// Meta 名称
	private $name = '';

	// 文章 ID
	private $pid = 0;

	// Meta ID
	private $mid = 0;

	/**
	 * @brief setType 设置 Meta 类型
	 *
	 * @param $type Meta 类型
	 *
	 * @return void
	 */
	public function setType( $type ) {
		$this->type = $type;
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
	 * @brief setAlias 设置 Meta 别名
	 *
	 * @param $alias Meta 别名
	 *
	 * @return void
	 */
	public function setAlias( $alias ) {
		$this->alias = $alias;
	}

	/**
	 * @brief setName 设置 Meta 名称
	 *
	 * @param $name Meta 名称
	 *
	 * @return void
	 */
	public function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * @brief setMID 设置 Meta ID
	 *
	 * @param $mid Meta ID
	 *
	 * @return void
	 */
	public function setMID( $mid ) {
		$this->mid = intval( $mid );
	}

	/**
	 * @brief getMeta 根据条件获取 Meta 信息
	 *
	 * @param $child 是否根据层级重组返回数组
	 *
	 * @return array
	 */
	public function getMeta( $child = TRUE ) {
		$pid = $this->pid;
		$alias = $this->alias;
		$name = $this->name;
		$mid = $this->mid;
		$type = $this->type;

		$where = ' 1=1';

		if( $pid ) {
			$where .= " AND R.pid={$pid}";
		}
		if( $alias ) {
			$where .= " AND M.alias='{$alias}'";
		}
		if( $name ) {
			$where .= " AND M.name='{$name}'";
		}
		if( $mid ) {
			$where .= " AND M.mid={$mid}";
		}
		if( $type ) {
			$where .= " AND M.type={$type}";
		}

		// 取出 Meta 数组
		if( $pid ) {
			$metas = Database::fetchAll("SELECT M.* FROM `{$this->prefix}posts_relation` AS R, `{$this->prefix}posts_meta` AS M WHERE {$where} AND R.mid=M.mid GROUP BY M.mid");
		} else {
			$metas = Database::fetchAll("SELECT * FROM `{$this->prefix}posts_meta` AS M WHERE {$where}");
		}

		if( ( count( $metas ) > 1 ) && $child ) {
			return $this->childMeta( $metas, 0 );
		} else {
			return $metas;
		}
	}

	/**
	 * @brief childMeta 获取某条目的全部子条目
	 *
	 * @param $metas Meta 数组
	 *
	 * @return array
	 */
	public function childMeta( $metas, $mid ) {
		$m = array();
		foreach( $metas as $k => $v ) {
			if( $v['parent'] == $mid ) {
				$m[] = $v;
			}
		}
		foreach( $m as $k => $v ) {
			$m[$k]['child'] = $this->childMeta( $metas, $v['mid'] );
		}
		return $m;
	}

	/**
	 * @brief addRelation 添加 Meta 与 Post 的对应关系
	 *
	 * @param $mid Meta ID
	 * @param $pid Post ID
	 *
	 * @return bool
	 */
	public function addRelation( $mid, $pid ) {
		if( !Database::fetchOneArray("SELECT * FROM `{$this->prefix}posts_relation` WHERE `pid`={$pid} AND `mid`={$mid}",FALSE) ) {
			$this->incReply( $mid );
			return Database::query("INSERT INTO `{$this->prefix}posts_relation` (`pid`,`mid`) VALUES ({$pid},{$mid})");
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief delRelation 删除 Meta 与 Post 的对应关系
	 *
	 * @param $mid Meta ID
	 * @param $pid Post ID
	 *
	 * @return bool
	 */
	public function delRelation( $mid, $pid ) {
		if( Database::fetchOneArray("SELECT * FROM `{$this->prefix}posts_relation` WHERE `pid`={$pid} AND `mid`={$mid}",FALSE) ) {
			$this->decReply( $mid );
			return Database::query("DELETE FROM `{$this->prefix}posts_relation` WHERE `pid`={$pid} AND `mid`={$mid}");
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief movRelation 修改 Meta 与 Post 的对应关系
	 *
	 * @param $mid Meta ID
	 * @param $oldPID Post ID
	 * @param $newPID Post ID
	 * @param $isMovePID 移动的是否为 PID
	 *
	 * @return bool
	 */
	public function movRelation( $mid, $oldPID, $newPID, $isMovePID = true ) {
		if( $isMovePID ) {
			if( $r = Database::fetchOneArray("SELECT * FROM `{$this->prefix}posts_relation` WHERE `pid`={$oldPID} AND `mid`={$mid}",FALSE) ) {
				return Database::query("UPDATE `{$this->prefix}posts_relation` SET `pid`={$newPID} WHERE `rid`={$r['rid']}");
			} else {
				return FALSE;
			}
		} else {
			if( $r = Database::fetchOneArray("SELECT * FROM `{$this->prefix}posts_relation` WHERE `pid`={$mid} AND `mid`={$oldPID}",FALSE) ) {
				$this->incReply( $newPID );
				$this->decReply( $oldPID );
				return Database::query("UPDATE `{$this->prefix}posts_relation` SET `mid`={$newPID} WHERE `rid`={$r['rid']}");
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * @brief incReply 增加 Meta 的文章计数
	 *
	 * @param $mid Meta ID
	 *
	 * @return void
	 */
	public function incReply( $mid ) {
		return Database::query("UPDATE `{$this->prefix}posts_meta` SET `reply`=`reply`+1 WHERE `mid`={$mid}",FALSE);
	}

	/**
	 * @brief decReply 减少 Meta 的文章计数
	 *
	 * @param $mid Meta ID
	 *
	 * @return void
	 */
	public function decReply( $mid ) {
		return Database::query("UPDATE `{$this->prefix}posts_meta` SET `reply`=`reply`-1 WHERE `mid`={$mid}",FALSE);
	}

	/**
	 * @brief addMeta 添加一个 Meta
	 *
	 * @param $m 
	 *
	 * @return mix
	 */
	public function addMeta( $m ) {
		$m['type'] = isset( $m['type'] ) ? $m['type'] : 1;
		$m['name'] = isset( $m['name'] ) ? $m['name'] : '';
		$m['alias'] = isset( $m['alias'] ) ? $m['alias'] : '';
		$m['description'] = isset( $m['description'] ) ? $m['description'] : '';
		$m['parent'] = isset( $m['parent'] ) ? $m['parent'] : 0;

		// 检查数据完整性
		if( $m['type'] == 1 || $m['type'] == 2 ) {
			// 分类
			if( $m['type'] == 1 && ( $m['name'] == '' || $m['alias'] == '' ) ) {
				return FALSE;
			}
			// 标签
			if( $m['type'] == 2 && $m['name'] == '' ) {
				return FALSE;
			}

			// 检查重复
			$this->name = $m['name'];
			if( $this->getMeta() ) {
				return FALSE;
			}
			$this->name = '';
			$this->alias = $m['alias'];
			if( $this->alias && $this->getMeta() ) {
				return FALSE;
			}
		}

		// 写入数据库
		Database::query("INSERT INTO `{$this->prefix}posts_meta` 
			(`name`,        `description`,        `alias`,        `type`,      `top`,`reply`,`parent`) VALUES 
			('{$m['name']}','{$m['description']}','{$m['alias']}',{$m['type']},0,    0,      {$m['parent']})");
		return Database::insertID();
	}

	/**
	 * @brief editMeta 编辑一个 Meta
	 *
	 * @param $m 
	 *
	 * @return bool
	 */
	public function editMeta( $m ) {
		$m['mid'] = isset( $m['mid'] ) ? $m['mid'] : 0;
		$m['type'] = isset( $m['type'] ) ? $m['type'] : 1;
		$m['name'] = isset( $m['name'] ) ? $m['name'] : '';
		$m['alias'] = isset( $m['alias'] ) ? $m['alias'] : '';
		$m['description'] = isset( $m['description'] ) ? $m['description'] : '';
		$m['parent'] = isset( $m['parent'] ) ? $m['parent'] : 0;

		// 检查数据完整性
		if( $m['type'] == 1 && ( !$m['mid'] || $m['name'] == '' || $m['alias'] == '' ) ) {
			return FALSE;
		}
		if( $m['type'] == 2 && ( !$m['mid'] || $m['name'] == '' ) ) {
			return FALSE;
		}

		// 检查重复
		$this->name = $m['name'];
		if( ( $temp = $this->getMeta() ) && $temp[0]['mid'] != $m['mid'] ) {
			return FALSE;
		}
		$this->name = '';
		$this->alias = $m['alias'];
		if( $this->alias && ( $temp = $this->getMeta() ) && $temp[0]['mid'] != $m['mid'] ) {
			return FALSE;
		}

		// 写入数据库
		Database::query("UPDATE `{$this->prefix}posts_meta` SET `name`='{$m['name']}', `description`='{$m['description']}', `alias`='{$m['alias']}', `type`={$m['type']}, `parent`={$m['parent']} WHERE `mid`={$m['mid']}");
		return TRUE;
	}

	/**
	 * @brief delMeta 删除一个 Meta
	 *
	 * @param $mid
	 *
	 * @return bool
	 */
	 public function delMeta( $mid ) {
	 	Database::query("DELETE FROM `{$this->prefix}posts_relation` WHERE `mid`={$mid}");
	 	Database::query("DELETE FROM `{$this->prefix}posts_meta` WHERE `mid`={$mid}");
	 	// TODO 删除附件的时候同时删除文件
	 	return TRUE;
	 }

}
