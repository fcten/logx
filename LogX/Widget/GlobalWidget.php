<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class GlobalWidget extends Widget {

	// 全局标题
	public $title = '';

	// 全局描述
	public $description = '';

	// 全局关键词
	public $keywords = '';

	/**
	 * @brief title 输出博客标题
	 *
	 * @return void
	 */
	public function title() {
		if( $this->title ) {
			$title = $this->title . ' &lsaquo; ';
		} else {
			$title = '';
		}
		$title .= OptionLibrary::get('title');
		echo Plugin::call( 'title', $title );
	}

	/**
	 * @brief name 输出网站名
	 *
	 * @return void
	 */
	public function name() {
		$title = OptionLibrary::get('title');
		echo Plugin::call( 'name', $title );
	}

	/**
	 * @brief description 输出博客描述
	 *
	 * @return void
	 */
	public function description() {
		$description = OptionLibrary::get('description');
		echo Plugin::call( 'description', $description );
	}

	/**
	 * @brief keywords 输出网站关键词
	 *
	 * @return void
	 */
	public function keywords() {
		$keywords = OptionLibrary::get('keywords');
		echo Plugin::call( 'keywords', $keywords );
	}

	/**
	 * @brief head 输出头部信息
	 *
	 * @return void
	 */
	public function head() {
		$head = '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />'."\n";
		$head .= '<meta name="description" content="';
		$head .= $this->description ? $this->description : OptionLibrary::get('description');
		$head .= '" />'."\n";
		$head .= '<meta name="keywords" content="';
		$head .= $this->keywords ? $this->keywords . ',' . OptionLibrary::get('keywords') : OptionLibrary::get('keywords');
		$head .= '" />'."\n";
		$head .= '<meta name="generator" content="LogX V'.LOGX_VERSION.'" />'."\n";
		echo Plugin::call( 'head', $head );
	}

	/**
	 * @brief foot 输出底部信息
	 *
	 * @return void
	 */
	public function foot() {
		echo Plugin::call( 'foot', '' );
	}

	/**
	 * @brief path 输出路径信息
	 *
	 * @param $path 相对路径
	 * @param $base 基路径
	 *
	 * @return void
	 */
	public function path( $path = '', $base = 'base' ) {
		if( $base == 'theme' ) {
			$themePath = str_replace( LOGX_ROOT, '', LOGX_THEME );
			echo LOGX_PATH . $themePath . $this->theme . '/' . $path;
		} elseif( $base == 'base' ) {
			echo LOGX_PATH . $path;
		} else {
			echo Router::patch( $base, $path );
		}
	}

	/**
	 * @brief is 判断当前访问位置
	 *
	 * @param $key 路由名
	 * @param $params 参数
	 *
	 * @return bool
	 */
	public function is( $key, $params = array() ) {
		if( count( $params ) ) {
			$p = Router::getCurrentParams();
			foreach( $params as $k => $v ) {
				if( !isset( $p[$k] ) || $v != $p[$k] ) {
					return FALSE;
				}
			}
		}
		if( $key == Router::getCurrentRoute() ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @brief runInfo 输出运行信息
	 *
	 * @return void
	 */
	public function runInfo() {
		$run_time = number_format( microtime( TRUE ) - LogX::$_globalVars['RUN']['TIME'], 4 ) * 1000;
		// DEBUG 模式下输出额外信息
		if( defined('LOGX_DEBUG') ) {
			Log::add( _t( 'Process time:' ) . $run_time . 'ms.', E_USER_NOTICE );
			if( function_exists( 'memory_get_usage' ) ) {
				$app_mem = memory_get_usage() - LogX::$_globalVars['RUN']['MEM'];
				Log::add( _t( 'Memory usage:' ) . $app_mem / 1024 . 'KB.', E_USER_NOTICE );
			}
			if( function_exists( 'memory_get_peak_usage' ) ) {
				$app_mem = memory_get_peak_usage();
				Log::add( _t( 'Max memory usage:' ) . $app_mem / 1024 . 'KB.', E_USER_NOTICE );
			}
			Log::add( _t( 'Queries:' ) . Database::$querynum . '. ' . _t( 'Query time:' ) . Database::$querytime * 1000 . 'ms.', E_USER_NOTICE );

			$content  = "以下是应用运行过程中的跟踪信息：\n\n";
			$content .= '当前页面：' . Request::S( 'REQUEST_URI', 'string' ) . "\n";
			$content .= '请求方法：' . Request::S( 'REQUEST_METHOD', 'string' ) . "\n";
			$content .= '通信协议：' . Request::S( 'SERVER_PROTOCOL', 'string' ) . "\n";
			$content .= '请求时间：' . date( 'Y-m-d H:i:s', Request::S( 'REQUEST_TIME', 'string' ) ) . "\n";
			$content .= '用户代理：' . Request::S( 'HTTP_USER_AGENT', 'string' ) . "\n";
			$content .= "\n日志记录：\n\n";

			foreach( Log::get() as $log ) {
				$content .= '[' . $log['LEVEL'] . '] ' . $log['MESSAGE'] . "\n";
			}
			$files =  get_included_files();
			$content .= "\n加载文件：" . str_replace("\n","\n",substr(substr(print_r($files,true),7),0,-2));
			if( function_exists( 'debug_backtrace' ) ) {
				$content .= "\nBACKTRACE：\n\n" . var_export( debug_backtrace(),true );
			}

			echo $content;
		} else {
			echo 'Processed in ' . $run_time . ' ms, ' . Database::$querynum . ' queries.';
		}
	}

}

function title() {
	return Widget::getWidget('Global')->title();
}

function name() {
	return Widget::getWidget('Global')->name();
}

function path( $path = '', $base = 'base' ) {
	return Widget::getWidget('Global')->path( $path, $base );
}

function display( $path ) {
	return Widget::getWidget('Global')->display( $path );
}

function head() {
	return Widget::getWidget('Global')->head();
}

function foot() {
	return Widget::getWidget('Global')->foot();
}

function description() {
	return Widget::getWidget('Global')->description();
}

function keywords() {
	return Widget::getWidget('Global')->keywords();
}

function logo() {

}

function is( $key, $params = array() ) {
	return Widget::getWidget('Global')->is( $key, $params );
}

function run_info() {
	return Widget::getWidget('Global')->runInfo();
}
