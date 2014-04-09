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

	public static $buffer = array();

	public static function connect($host,$user,$pwd,$dbname,$pconnect = false) {
		self::$link = $pconnect ? @mysql_pconnect($host,$user,$pwd) : @mysql_connect($host,$user,$pwd);
		if ( !self::$link ) {
			self::halt("Can't connect to database!");
		}
		if ( !mysql_select_db($dbname, self::$link) ) {
			self::halt("Can't use database {$dbname}！");
		}
		if (self::version() > '4.1') {
			mysql_query('SET NAMES utf8;',self::$link);
		}
	}

	public static function query($sql, $cache = true, $unbuffer = false ) {
		if( $cache && isset( self::$buffer[$sql] ) ) {
			$query = self::$buffer[$sql];
		} else {
			$runtime = microtime( TRUE );
			if ( $unbuffer && function_exists('mysql_unbuffered_query') ) {
				$query = @mysql_unbuffered_query($sql, self::$link);
			} else {
				$query = self::$buffer[$sql] = @mysql_query($sql,self::$link);
			}
			$runtime = microtime( TRUE ) - $runtime;
			//[DEBUG]
			if( defined( 'LOGX_DEBUG' ) ) {
				Log::add( 'SQL: ' . $sql . ' TIME: ' . number_format( $runtime, 4 ) * 1000 . 'ms.', E_USER_WARNING );
			}
			//[/DEBUG]
			self::$querynum ++;
			self::$querytime += $runtime;
		}
		!$query && self::halt('SQL Error！',$sql);
		return $query;
	}

	private static function fetchArray($query,$result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query,$result_type);
	}

	public static function fetchOneArray($sql,$cache = true) {
		$query = self::query($sql,$cache);
		if( $result = self::fetchArray($query) ) {
			mysql_data_seek($query, 0);
		}
		return $result;
	}

	public static function fetchAll($sql,$cache = true) {
		$result = array();
		$query = self::query($sql,$cache);
		while($row = self::fetchArray($query)) {
			$result[] = $row;
		}
		if( count($result) ) {
			mysql_data_seek($query, 0);
		}
		return $result;
	}

	public static function result($sql,$cache = true, $row = 0) {
		$query = self::query($sql,$cache);
		if( @mysql_num_rows( $query ) > 0 ) {
			return @mysql_result( $query, $row );
		}
	}

	public static function freeResult($query) {
		return mysql_free_result($query);
	}

	public static function insertId() {
		return ($id = mysql_insert_id(self::$link)) >= 0 ? $id : self::result(self::query("SELECT last_insert_id()",false), 0);
	}

	public static function numFields($query) {
		return mysql_num_fields($query);
	}

	public static function numRows($query) {
		return mysql_num_rows($query);
	}

	public static function affectedRows() {
		return mysql_affected_rows(self::$link);
	}

	public static function close() {
		return @mysql_close(self::$link);
	}

	public static function version() {
		return mysql_get_server_info(self::$link);
	}

	public static function halt($msg = '',$sql = '') {
		$output = $msg.'<br /><br />Error SQL：'.$sql.'<br />Error Code：'.mysql_errno().'<br />Error Tips：'.mysql_error();
		Response::error( _t('Database Error'), $output );
	}

}
