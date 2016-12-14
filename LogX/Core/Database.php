<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class Database {

	public static $link;

	public static $querynum = 0;

	public static $querytime = 0;

	/*****************/

	public static $sql = array();

	private static $_trans_times = 0;

	public static function connect($host,$user,$pwd,$dbname) {
		self::$link = mysqli_connect($host,$user,$pwd,$dbname);
		if ( !self::$link ) {
			self::halt("Can't connect to database!");
		}
		if (self::version() > '4.1') {
			mysqli_query(self::$link, 'SET NAMES utf8;');
		}
	}

	public static function query($sql) {
		$runtime = microtime( TRUE );
		$query = mysqli_query(self::$link, $sql);
		$runtime = microtime( TRUE ) - $runtime;
		//[DEBUG]
		if( defined( 'LOGX_DEBUG' ) ) {
			Log::add( 'SQL: ' . $sql . ' TIME: ' . number_format( $runtime, 4 ) * 1000 . 'ms.', E_USER_WARNING );
		}
		//[/DEBUG]
		self::$querynum ++;
		self::$querytime += $runtime;

		!$query && self::halt('SQL Error！',$sql);
		return $query;
	}

	private static function fetchArray($query,$result_type = MYSQLI_ASSOC) {
		return mysqli_fetch_array($query,$result_type);
	}

	public static function fetchOneArray($sql) {
		$query = self::query($sql);
		if( $result = self::fetchArray($query) ) {
			mysqli_data_seek($query, 0);
		}
		return $result;
	}

	public static function result($sql) {
		$data = self::fetchOneArray( $sql );
		if ($data) {
		    $tmp = @array_keys( $data );
			return $data[ @reset($tmp) ];
		} else {
			return false;
		}
	}

	public static function fetchAll($sql) {
		$result = array();
		$query = self::query($sql);
		while($row = self::fetchArray($query)) {
			$result[] = $row;
		}
		if( count($result) ) {
			mysqli_data_seek($query, 0);
		}
		return $result;
	}

	public static function freeResult($query) {
		return mysqli_free_result($query);
	}

	public static function insertId() {
		return ($id = mysqli_insert_id(self::$link)) >= 0 ? $id : self::result(self::query("SELECT last_insert_id()",false), 0);
	}

	public static function numFields($query) {
		return mysqli_num_fields($query);
	}

	public static function numRows($query) {
		return mysqli_num_rows($query);
	}

	public static function affectedRows() {
		return mysqli_affected_rows(self::$link);
	}

	public static function close() {
		return @mysqli_close(self::$link);
	}

	public static function version() {
		return mysqli_get_server_info(self::$link);
	}

	public static function escape( $str ) {
		return mysqli_real_escape_string(self::$link, $str);
	}
	
    /**
     * 启动事务
     * @access public
     * @return void
     */
    public static function startTrans() {
        //数据rollback 支持
        if (self::$_trans_times == 0) {
            mysqli_query(self::$link, 'START TRANSACTION');
        }
        self::$_trans_times ++;
        return ;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @access public
     * @return boolean
     */
    public static function commit() {
        if ( self::$_trans_times > 0 ) {
            $result = mysqli_query(self::$link, 'COMMIT');
            self::$_trans_times = 0;
            if(!$result){
                self::halt("Commit failed！");
                return false;
            }
        }
        return true;
    }

    /**
     * 事务回滚
     * @access public
     * @return boolean
     */
    public static function rollback() {
        if ( self::$_trans_times > 0 ) {
            $result = mysqli_query(self::$link, 'ROLLBACK');
            self::$_trans_times = 0;
            if(!$result){
                self::halt("Rollback failed！");
                return false;
            }
        }
        return true;
    }

	public static function halt($msg = '',$sql = '') {
		$output = $msg.'<br /><br />Error SQL：'.$sql.'<br />Error Code：'.mysqli_errno().'<br />Error Tips：'.mysqli_error();
		Response::error( _t('Database Error'), $output );
	}

}
