<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class Compile {

	public static function build( $path, $files, $name = 'logx' ) {
		$content = '';
		foreach( $files as $file ) {
			$content .= self::compiler( $file );
		}
		if( !@file_put_contents( $path . '~' . $name . '.php', self::stripWhiteSpace( '<?php ' . $content . '?>' ) ) ) {
			throw new LogXException( _t('Cache directory cannot write.') );
		}
		unset( $content );
	}

	// 编译文件
	private static function compiler( $filename ) {
		$content = file_get_contents( $filename );
		// 替换预编译指令
		if( !defined( 'LOGX_DEBUG' ) ) {
			$content = preg_replace( '/\/\/\[DEBUG\](.*?)\/\/\[\/DEBUG\]/s','',$content );
		}
		$content = substr( trim( $content ), 5 );
		if( '?>' == substr( $content, -2 ) ) {
			$content = substr( $content, 0, -2 );
		}
		return $content;
	}

	// 去除代码中的空白和注释
	private static function stripWhiteSpace( $content ) {
		$stripStr = '';
		//分析php源码
		$tokens = token_get_all( $content );
		$last_space = false;
		for( $i = 0, $j = count( $tokens ) ; $i < $j ; $i ++ ) {
			if( is_string( $tokens[$i] ) ) {
				$last_space = false;
				$stripStr .= $tokens[$i];
			} else {
				switch( $tokens[$i][0] ) {
				//过滤各种PHP注释
				case T_COMMENT:
				case T_DOC_COMMENT:
					break;
				//过滤空格
				case T_WHITESPACE:
					if (!$last_space) {
						$stripStr .= ' ';
						$last_space = true;
					}
					break;
				default:
					$last_space = false;
					$stripStr .= $tokens[$i][1];
				}
			}
		}
		return $stripStr;
	}

}
