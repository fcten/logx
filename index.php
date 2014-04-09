<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

// 检查安装
if (!@include './config.php') {
	file_exists('./install.php') ? header('Location: install.php') : print('Config file missing.');
	exit;
}

// LogX 根路径
define( 'LOGX_ROOT', str_replace( '\\', '/', dirname( __FILE__ ) ) . '/' );

// LogX WEB 路径
define( 'LOGX_PATH', str_replace( 'index.php', '', $_SERVER[ 'SCRIPT_NAME' ] ) );

// 载入 LogX 配置
if (!@include LOGX_ROOT.'LogX/Config.php') {
	die('LogX Config file missing.');
}

// 载入系统文件
if( defined( 'LOGX_DEBUG' ) || !file_exists( LOGX_CACHE . '~core.php' ) ) {
	foreach( $coreFiles as $file ) {
		if( !@include $file ) {
			die('Core files missing.');
		}
	}
} else {
	// 载入编译缓存
	include LOGX_CACHE . '~core.php';
}

// 启动 LogX
LogX::init();
