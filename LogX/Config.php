<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */
 
 // LogX 版本信息
define( 'LOGX_VERSION', '0.9.0' );

// LogX 调试开关
// 关闭调试时注释掉此行，而不是将它定义为 false
define( 'LOGX_DEBUG', true );

// 缓存类型
// 系统默认支持 FILE, SHMOP, MEMCACHE 三种类型（需要服务器支持）
define( 'CACHE_TYPE', 'FILE' );

// 缓存有效时间，单位秒
define( 'CACHE_TIMEOUT', 30 );

// LogX 路径定义
define( 'LOGX_CORE',   LOGX_ROOT . 'LogX/Core/' );
define( 'LOGX_WIDGET', LOGX_ROOT . 'LogX/Widget/' );
define( 'LOGX_LIB',    LOGX_ROOT . 'LogX/Library/' );
define( 'LOGX_PLUGIN', LOGX_ROOT . 'User/Plugin/' );
define( 'LOGX_THEME',  LOGX_ROOT . 'User/Theme/' );
define( 'LOGX_CACHE',  LOGX_ROOT . 'User/Cache/' );
define( 'LOGX_FILE',   LOGX_ROOT . 'User/File/' );
define( 'LOGX_LANG',   LOGX_ROOT . 'User/Lang/' );

// 日志等级
// 这些等级的日志会被写入 log 文件
define( 'LOG_LEVEL', 0 );

// 内核文件
$coreFiles = array(
	LOGX_CORE . 'Cache.php',
	LOGX_CORE . 'Compile.php',
	LOGX_CORE . 'Database.php',
	LOGX_CORE . 'Exception.php',
	LOGX_CORE . 'Language.php',
	LOGX_CORE . 'Library.php',
	LOGX_CORE . 'Log.php',
	LOGX_CORE . 'LogX.php',
	LOGX_CORE . 'Plugin.php',
	LOGX_CORE . 'Request.php',
	LOGX_CORE . 'Response.php',
	LOGX_CORE . 'Router.php',
	LOGX_CORE . 'Theme.php',
	LOGX_CORE . 'Widget.php'
);
