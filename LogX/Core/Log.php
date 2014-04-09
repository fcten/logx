<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class Log {

	// 记录的日志信息
	private static $log = array();

	/**
	 * @brief add 添加一条日志
	 *
	 * @param $message 日志信息
	 * @param $level 日志等级
	 *
	 * @return void
	 */
	public static function add( $message, $level = 1024 ) {
		if( self::checkErrorLevel( LOG_LEVEL, $level ) ) {
			self::write( $message, $level );
		}
		self::$log[] = array( 'MESSAGE' => $message, 'LEVEL' => $level );
	}

	/**
	 * @brief get 获取全部日志信息
	 *
	 * @return array
	 */
	public static function get() {
		return self::$log;
	}

	/**
	 * @brief write 讲日志信息写入文件
	 *
	 * @param $message 日志信息
	 * @param $level 日志等级
	 *
	 * @return void
	 */
	private static function write( $message, $level ) {
		$file_path = LOGX_CACHE . date( 'Y-m-d' ) . '.php';

		if( !is_file( $file_path ) ) {
			if( !@file_put_contents( $file_path , "<?php exit('Access Denied!'); ?>\n" ) ) {
				throw new LogXException( _t('Cache directory cannot write.') );
			}
		}
		$content = '[' . Request::getIP() . '] [' . date( 'Y-m-d H:i:s' ) . '] [' . $level . '] ' . $message . "\n";
		if( !@file_put_contents( $file_path , $content, FILE_APPEND ) ) {
			throw new LogXException( _t('Cache directory cannot write.') );
		}
	}

	/**
	 * @brief checkErrorLevel 检查错误等级
	 *
	 * @param $level 接受的等级
	 * @param $num 被检查的等级
	 *
	 * @return 
	 */
	private static function checkErrorLevel( $level, $num ) {
		$i = 4096;
		while( $i >= 1 ) {
			if( $level >= $i ) {
				if( $num == $i ) return TRUE;
				$level -= $i;
			}
			$i = $i / 2;
		}
		return FALSE;
	}

}
