<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

// LogX 根路径
define( 'LOGX_ROOT', str_replace( '\\', '/', dirname( __FILE__ ) ) . '/' );

// LogX WEB 路径
define( 'LOGX_PATH', str_replace( 'install.php', '', $_SERVER[ 'SCRIPT_NAME' ] ) );

// 载入 LogX 配置
if (!@include_once LOGX_ROOT.'LogX/Config.php') {
	die('LogX Config file missing.');
}

// 载入系统文件
foreach( $coreFiles as $file ) {
	if( !@include_once $file ) {
		die('Core files missing.');
	}
}

// 连接到缓存
Cache::connect( CACHE_TYPE );

// 检查安装
if( file_exists('./config.php') ) {
	showError("<strong>检测到重复安装请求</strong><br />您已经安装过 LogX，为保证您的数据安全，安装程序自动拒绝了此安装请求。<br>如果您确实希望重新安装 LogX，请先删除 LogX 程序根目录下的 config.php 文件，再重新运行安装程序。");
}

// 执行安装程序
switch( Request::G('step') ) {
	case 1:
		showHead();

		// 检查系统环境
		$result = TRUE;
		if ( !version_compare( PHP_VERSION, '5.0.0', '>=' ) ) {
			$result = FALSE;
		}
		if ( !function_exists('mysql_connect') ) {
			$result = FALSE;
		}
		if( !$result ) {
?>
		<div class="error">
			<strong>系统环境检查未通过</strong><br />
			为了保证 LogX 的基本功能可以正常使用，您所使用的服务器至少需要通过前两项系统环境检查。<br />
			如果您希望能够享受 LogX 为您提供的附加功能，您的服务器最好能通过全部被检测项目。
		</div>

		<table class="datatable">
			<tr style="font-weight:bold;"><td>名称</td><td>是否必须</td><td>说明</td><td>检测结果</td></tr>
			<tr><td><strong>PHP 版本</strong></td><td>是</td><td>LogX 无法在 PHP 5 之前的旧版本 PHP 环境下运行</td><td><?php if ( version_compare( PHP_VERSION, '5.0.0', '>=' ) ) { ?><font color="blue">OK</font><?php } else { ?><font color="red">Failed</font><?php } ?></td></tr>
			<tr><td><strong>MySQL 支持</strong></td><td>是</td><td>LogX 目前只支持 MySQL 一种数据库类型</td><td><?php if ( function_exists('mysql_connect') ) { ?><font color="blue">OK</font><?php } else { ?><font color="red">Failed</font><?php } ?></td></tr>
			<tr><td><strong>GD 库</strong></td><td>否</td><td>为您的 LogX 提供图像处理支持</td><td><?php if ( function_exists('imagecreate') ) { ?><font color="blue">OK</font><?php } else { ?><font color="red">Failed</font><?php } ?></td></tr>
		</table>

		<form id="theform" method="post" action="install.php?step=1">
			<table class=button>
				<tr>
					<td><input type="submit" name="submit" value="重新检测系统环境"></td>
				</tr>
			</table>
		</form>
<?php
		} else {
			// 检查目录权限
			$arr = array(
				'./',
				'./User/Cache/',
				'./User/File/'
			);
			$result = TRUE;
			$re = array();
			foreach( $arr as $path ) {
				if( !writeable( LOGX_ROOT . $path ) ) {
					$re[$path] = '<font color="red">Failed</font>';
					$result = FALSE;
				} else {
					$re[$path] = '<font color="blue">OK</font>';
				}
			}
			if( !$result ) {
?>
		<div class="error">
			<strong>文件/目录权限检查未通过</strong><br />
			在您执行安装文件进行安装之前，先要设置相关的目录属性，以便 LogX 可以正确进行读、写、删、创建子目操作录。<br />
			推荐您这样做：使用 FTP 软件登录您的服务器，将服务器上以下目录、以及该目录下面的所有文件的属性设置为777，win主机请设置internet来宾帐户可读写属性
		</div>

		<table class="datatable">
			<tr style="font-weight:bold;"><td>名称</td><td>所需权限属性</td><td>说明</td><td>检测结果</td></tr>
			<tr><td><strong>./</strong></td><td>读、写</td><td>根目录（系统配置文件）</td><td><?php echo $re['./']; ?></td></tr>
			<tr><td><strong>./User/Cache/</strong></td><td>读、写、删</td><td>缓存目录</td><td><?php echo $re['./User/Cache/']; ?></td></tr>
			<tr><td><strong>./User/File/</strong></td><td>读、写、删</td><td>附件目录</td><td><?php echo $re['./User/File/']; ?></td></tr>
		</table>

		<form id="theform" method="post" action="install.php?step=1">
			<table class=button>
				<tr>
					<td><input type="submit" name="submit" value="重新检测目录权限"></td>
				</tr>
			</table>
		</form>
<?php
			} else {
?>
		<div class="notice">
			<strong>欢迎您使用 LogX</strong><br />
			LogX 是一款简约而优雅的个人博客系统。使用 LogX，任何人都可以快速开始美妙的独立博客之旅，和你的读者自由地交流互动，享受创作的快乐。<br />
			不仅如此，LogX 还是一款完全免费并开放的自由软件(<a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">LogX 遵循 GPL v2</a>)。通过加入 <a href="http://forum.logx.org/"  target="_blank">LogX 社区</a>，你可以左右 LogX 的新版本开发进程，使 LogX 变得更加完美！
		</div>

		<form id="theform" method="post" action="install.php?step=2">
			<table class=button>
				<tr>
					<td><input type="submit" name="submit" value="接受授权协议，开始安装 LogX"></td>
				</tr>
			</table>
		</form>
<?php
			}
		}
		showFoot();
		break;
	case 2:
		showHead();
?>
		<form id="theform" method="post" action="install.php?step=3">
			<div class="notice">
				<strong>设置数据库连接信息</strong><br />
				如果您不知道这里应该如何填写，您可以咨询您的主机提供商，或者去 <a href="http://forum.logx.org/"  target="_blank">LogX 社区</a> 发帖求助。
			</div>

			<table class="datatable">
				<tbody>
					<tr>
						<td width="25%">数据库服务器地址:</td>
						<td><input type="text" name="db[host]" size="20" value="localhost"></td>
						<td width="30%">一般为 localhost。</td>
					</tr>
					<tr>
						<td>数据库用户名:</td>
						<td><input type="text" name="db[user]" size="20" value=""></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>数据库密码:</td>
						<td><input type="password" name="db[pw]" size="20" value=""></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>数据库名:</td>
						<td><input type="text" name="db[name]" size="20" value=""></td>
						<td>如果不存在，LogX 会尝试自动创建。但是虚拟主机一般不支持自动创建。</td>
					</tr>
					<tr>
						<td>数据表前缀:</td>
						<td><input type="text" name="db[prefix]" size="20" value="logx_"></td>
						<td>如果您不知道此项的作用，请保留默认设置。</td>
					</tr>
				</tbody>
			</table>

			<table class="button">
				<tbody><tr><td><input type="submit" name="submit" value="设置完毕, 进入下一步"></td></tr></tbody>
			</table>
		</form>
<?php
		showFoot();
		break;
	case 3:
		if( Request::P('submit','string') ) {
			$db = Request::P('db','array');
			if( !$db['host'] || !$db['user'] || !$db['name'] || !$db['prefix'] ) {
				showError('<strong>您的填写不完整</strong><br />除了数据库密码之外的项目都是必填项目。','install.php?step=2');
			} else {
				if ( !$link = @mysql_connect( $db['host'], $db['user'], $db['pw'] ) ) {
					showError( '<strong>尝试连接到数据库失败</strong><br />请检查数据库是否正常启动，以及数据库地址、数据库用户名、数据库密码等信息是否正确！','install.php?step=2' );
				} else {
					if ( !@mysql_select_db( $db['name'], $link) ) {
						if ( !@mysql_query("CREATE DATABASE `{$db['name']}`", $link) ) {
							showError( '<strong>指定的数据库不存在</strong><br />您指定的数据库不存在，而且数据库用户没有创建该数据库的权限。','install.php?step=2' );
						}
					}
					Cache::set('InstallInfomation',$db,0);
				}
			}
		}
		showHead();
?>
		<form id="theform" method="post" action="install.php?step=4">
			<div class="notice">
				<strong>为您的博客取一个名字</strong><br />
				安装完成之后您仍然可以修改博客的名称，所以安啦安啦~
			</div>

			<table class="datatable">
				<tbody>
					<tr>
						<td width="25%">博客名称:</td>
						<td><input type="text" name="name" size="20" value=""></td>
						<td width="30%">&nbsp;</td>
					</tr>
				</tbody>
			</table>

			<table class="button">
				<tbody><tr><td><input type="submit" name="submit" value="好了，继续下一步"></td></tr></tbody>
			</table>
		</form>
<?php
		showFoot();
		break;
	case 4:
		if( Request::P('submit','string') ) {
			$info = Cache::get('InstallInfomation');
			$info['blog'] = Request::P('name','string');
			Cache::set('InstallInfomation',$info,0);
		}
		showHead();
?>
		<form id="theform" method="post" action="install.php?step=5">
			<div class="notice">
				<strong>添加一个管理员帐户</strong><br />
				现在添加的管理员帐户拥有系统的最高权限，请注意妥善保存密码。
			</div>

			<table class="datatable">
				<tbody>
					<tr>
						<td width="25%">用户名:</td>
						<td><input type="text" name="username" size="20" value=""></td>
						<td width="30%">&nbsp;</td>
					</tr>
					<tr>
						<td>邮箱:</td>
						<td><input type="text" name="email" size="20" value=""></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>密码:</td>
						<td><input type="password" name="password" size="20" value=""></td>
						<td>一个好的密码，长度至少为八位，并且同时包含数字、符号与大小写字母。</td>
					</tr>
					<tr>
						<td>确认密码:</td>
						<td><input type="password" name="repassword" size="20" value=""></td>
						<td>请再输入一遍您设置的密码。</td>
					</tr>
				</tbody>
			</table>

			<table class="button">
				<tbody><tr><td><input type="submit" name="submit" value="好了，继续下一步"></td></tr></tbody>
			</table>
		</form>
<?php
		showFoot();
		break;
	case 5:
		if( Request::P('submit','string') ) {
			if( Request::P('password','string') != Request::P('repassword','string') ) {
				showError('<strong>您两次输入的密码不一致</strong><br />请返回重新输入。','install.php?step=4');
			}
			if( !Request::P('username','string') || !Request::P('password','string') || !Request::P('email','string') ) {
				showError('<strong>用户名、邮箱、密码均为必填</strong><br />请返回重新输入。','install.php?step=4');
			}
			$info = Cache::get('InstallInfomation');
			$info['admin'] = Request::P('username','string');
			$info['email'] = Request::P('email','string');
			$info['adminpw'] = md5( Request::P('password','string') );
			$info['domain'] = Request::getDomain();
			Cache::delete('InstallInfomation');

			// 连接到数据库
			Database::connect( $info['host'], $info['user'], $info['pw'], $info['name'] );

			// 构建数据库
                        $scripts = file_get_contents ( LOGX_ROOT . 'install.php' );
                        preg_match('/-- Database '.'Structure Begin --(.*)-- Database '.'Structure End --/s', $scripts, $match);
                        $scripts = $match[1];
                        $scripts = str_replace('logx_', $info['prefix'], $scripts);
                        $scripts = explode(';', $scripts);
                        foreach ($scripts as $script) {
                            $script = str_replace( array('{time}','{blogname}','{admin}','{adminpw}','{domain}','{email}'), array(time(),$info['blog'],$info['admin'],$info['adminpw'],$info['domain'],$info['email']), trim($script) );
                            if ($script) {
                                Database::query($script);
                            }
                        }

			// 构建 config.php
			$config = "<?php\ndefine( 'DB_TYPE', 'mysql' );\ndefine( 'DB_HOST', '{$info['host']}' );\ndefine( 'DB_PORT', '3306' );\ndefine( 'DB_USER', '{$info['user']}' );\ndefine( 'DB_PWD', '{$info['pw']}' );\ndefine( 'DB_NAME', '{$info['name']}' );\ndefine( 'DB_PREFIX', '{$info['prefix']}' );\ndefine( 'DB_PCONNECT', FALSE );";
			file_put_contents( LOGX_ROOT . 'config.php', $config );
		}
		showHead();
?>
		<div class="success">
			<strong>开始您的 LogX 之旅</strong><br />
			恭喜您，一切皆已完成。请尽情享受 LogX 陪伴的创作之旅！<br /><br />
			<a href="./index.php" target="_blank">点击这里访问我的网站</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="./index.php/admin/" target="_blank">点击这里进行网站配置</a>
		</div>

		<div class="notice">
			<a target="_blank" href="http://forum.logx.org/">加入 LogX 社区, 帮助我们完善产品</a>
		</div>
<?php
		showFoot();
		break;
	default:
		header('Location: install.php?step=1');
}

function showError( $msg, $url = 'javascript:history.go(-1);' ) {
	showHead();
?>
		<div class="error">
			<?php echo $msg; ?>
			<br /><br /><a href="<?php echo $url; ?>">返回上一步</a>
		</div>
<?php
	showFoot();
	exit;
}

function showHead() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LogX 安装程序</title>
<style type="text/css">
* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
.bodydiv { text-align:left; background: #FFF; }
h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
#menu {width: 100%; margin: 10px auto; text-align: center; }
#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; width:20%; }
table{ width:100%; }
.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
.showtable { width:100%; border: solid; border-color:#86B9D6 #B2C9D3 #B2C9D3; border-width: 3px 1px 1px; margin: 10px auto; background: #F5FCFF; }
.showtable td { padding: 3px; }
.showtable strong { color: #5086A5; }
.datatable { width: 100%; margin: 10px auto 25px; }
.datatable td { padding: 5px 0; border-bottom: 1px solid #EEE; }
input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
.button { margin: 10px auto 20px; width: 100%; }
.button td { text-align: center; }
.button input, .button button { border: 1px solid #B2C9D3; padding: 5px 10px; color: #5086A5; background: #F5FCFF; cursor: pointer; }
#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
.error, .notice, .success {padding:.8em;margin-bottom:1em;border:1px solid #ddd;-moz-border-radius: 5px;-webkit-border-radius: 5px;border-radius: 5px;}
.error {background:#FBE3E4;color:#8a1f11;border-color:#FBC2C4;}
.notice {background:#FFF6BF;color:#514721;border-color:#FFD324;}
.success {background:#E6EFC2;color:#264409;border-color:#C6D880;}
.error a {color:#8a1f11;}
.notice a {color:#514721;}
.success a {color:#264409;}
.error a, .notice a, .success a {text-decoration: none; border-bottom-width: 1px; border-bottom-style: dashed;}
.error a:hover, .notice a:hover, .success a:hover {text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid;}
</style>
</head>
<body id="append_parent">
<div class="bodydiv">
	<h1>LogX 安装程序</h1>
	<div style="width:90%;margin:0 auto;">
		<table id="menu">
			<tr>
				<td<?php if( Request::G('step') == 1 ):?> class="current"<?php endif; ?>>1.欢迎使用 LogX</td>
				<td<?php if( Request::G('step') == 2 ):?> class="current"<?php endif; ?>>2.设置数据库连接信息</td>
				<td<?php if( Request::G('step') == 3 ):?> class="current"<?php endif; ?>>3.为您的博客取一个名字</td>
				<td<?php if( Request::G('step') == 4 ):?> class="current"<?php endif; ?>>4.添加一个管理员帐户</td>
				<td<?php if( Request::G('step') == 5 ):?> class="current"<?php endif; ?>>5.开始您的 LogX 之旅</td>
			</tr>
		</table>
<?php
}

function showFoot() {
?>
	</div>
	<div id="footer">LogX.org &copy; 2011</div>
</div>
</body>
</html>
<?php
}

function writeable( $var ) {
	$result = false;
	if ( is_dir( $var ) ) {
		$var .= 'temp.txt';
	}
	if ( ( $fp = @fopen( $var, 'wb' ) ) && ( @fwrite( $fp, 'LogX' ) ) ) {
		@fclose($fp);
		@unlink($var);
		$result = true;
	}
	return $result;
}

/*

-- Database Structure Begin --

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `logx_comments` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `pid` int(10) unsigned NOT NULL COMMENT '文章ID',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `author` varchar(255) NOT NULL COMMENT '作者',
  `email` varchar(255) NOT NULL COMMENT '邮箱',
  `website` varchar(255) NOT NULL COMMENT '主页',
  `content` text NOT NULL COMMENT '评论内容',
  `status` int(10) unsigned NOT NULL COMMENT '评论状态',
  `ptime` int(10) unsigned NOT NULL COMMENT '发布时间',
  `mtime` int(10) unsigned NOT NULL COMMENT '最后修改时间',
  `ip` varchar(255) NOT NULL COMMENT 'IP地址',
  `parent` int(10) unsigned NOT NULL COMMENT '上级评论',
  PRIMARY KEY (`cid`),
  KEY `pid` (`pid`,`uid`,`status`,`ptime`,`mtime`,`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='LogX 评论表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `logx_options` (
  `oid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '选项ID',
  `bid` int(10) unsigned NOT NULL COMMENT '博客ID',
  `name` varchar(255) NOT NULL COMMENT '键名',
  `value` text NOT NULL COMMENT '键值',
  `global` int(10) unsigned NOT NULL COMMENT '是否为全局值',
  PRIMARY KEY (`oid`),
  KEY `bid` (`bid`,`global`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='LogX 配置信息表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `logx_posts` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `uid` int(10) unsigned NOT NULL COMMENT '作者ID',
  `ptime` int(10) unsigned NOT NULL COMMENT '发布时间',
  `mtime` int(10) unsigned NOT NULL COMMENT '最后修改时间',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `alias` varchar(255) NOT NULL COMMENT '别名',
  `content` text NOT NULL COMMENT '正文',
  `type` int(10) unsigned NOT NULL COMMENT '文章类型',
  `status` int(10) unsigned NOT NULL COMMENT '文章状态',
  `allow_reply` int(10) unsigned NOT NULL COMMENT '是否允许评论',
  `top` int(10) unsigned NOT NULL COMMENT '置顶等级',
  `view` int(10) unsigned NOT NULL COMMENT '阅读次数',
  `reply` int(10) unsigned NOT NULL COMMENT '回复次数',
  PRIMARY KEY (`pid`),
  KEY `uid` (`uid`,`ptime`,`mtime`,`top`,`view`,`reply`,`status`,`type`,`allow_reply`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='LogX 文章表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `logx_posts_meta` (
  `mid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) NOT NULL COMMENT '名字',
  `description` varchar(255) NOT NULL COMMENT '描述',
  `alias` varchar(255) NOT NULL COMMENT '别名',
  `type` int(10) unsigned NOT NULL COMMENT '类型',
  `top` int(10) unsigned NOT NULL COMMENT '排序',
  `reply` int(10) unsigned NOT NULL COMMENT '文章数',
  `parent` int(10) unsigned NOT NULL COMMENT '上级条目ID',
  PRIMARY KEY (`mid`),
  KEY `type` (`type`,`top`,`reply`,`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='LogX 文章额外信息表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `logx_posts_relation` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(10) unsigned NOT NULL COMMENT '文章ID',
  `mid` int(10) unsigned NOT NULL COMMENT '信息ID',
  PRIMARY KEY (`rid`),
  KEY `pid` (`pid`,`mid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='LogX 文章信息关系表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `logx_users` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(255) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `group` int(10) unsigned NOT NULL COMMENT '用户组',
  `email` varchar(255) NOT NULL COMMENT '邮箱',
  `website` varchar(255) NOT NULL COMMENT '主页',
  `rtime` int(10) unsigned NOT NULL COMMENT '注册时间',
  `auth` varchar(255) NOT NULL COMMENT '安全验证',
  PRIMARY KEY (`uid`),
  KEY `group` (`group`,`rtime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='LogX 用户表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `logx_users_meta` (
  `mid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(10) unsigned NOT NULL COMMENT '用户 ID',
  `name` varchar(255) NOT NULL COMMENT '键名',
  `value` text NOT NULL COMMENT '键值',
  PRIMARY KEY (`mid`),
  KEY `uid` (`uid`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='LogX 用户额外信息表' AUTO_INCREMENT=1 ;

INSERT INTO `logx_posts` (`uid`, `ptime`, `mtime`, `title`, `alias`, `content`, `type`, `status`, `allow_reply`, `top`, `view`, `reply`) VALUES
(1, {time}, {time}, '开始您的 LogX 之旅', '', '<p>当您看到这篇文章，说明您的 LogX 已经安装成功。</p>\r\n<p>请删除或编辑这篇文章，开始您的 LogX 之旅。</p>', 1, 1, 1, 0, 1, 1),
(1, {time}, {time}, '关于', 'about', '<p>这是一个测试页面</p>', 2, 1, 1, 0, 1, 0);

INSERT INTO `logx_comments` (`pid`, `uid`, `author`, `email`, `website`, `content`, `status`, `ptime`, `mtime`, `ip`, `parent`) VALUES
(1, 0, 'LogX', 'admin@logx.org', 'http://www.logx.org/', '欢迎使用 LogX。', 1, {time}, {time}, 'Unknow', 0);

INSERT INTO `logx_options` (`bid`, `name`, `value`, `global`) VALUES
(1, 'title', '{blogname}', 1),
(1, 'description', '简单轻巧、优雅随心', 1),
(1, 'keywords', 'Free,Personal,Blog,PHP,MySQL,博客,LogX,个人博客,博客系统,博客程序', 1),
(1, 'theme', 'default', 1),
(1, 'rewrite', 'close', 1),
(1, 'timezone', 'Etc/GMT-8', 1),
(1, 'domain', '{domain}', 1),
(1, 'register', 'close', 1);

INSERT INTO `logx_posts_meta` (`name`, `description`, `alias`, `type`, `top`, `reply`, `parent`) VALUES
('默认分类', '这是一个默认分类', 'default', 1, 0, 1, 0),
('LogX', '', '', 2, 0, 1, 0),
('欢迎', '', '', 2, 0, 1, 0);

INSERT INTO `logx_posts_relation` (`pid`, `mid`) VALUES
(1, 1),
(1, 2),
(1, 3);

INSERT INTO `logx_users` (`username`, `password`, `group`, `email`, `website`, `rtime`, `auth`) VALUES
('{admin}', '{adminpw}', 10, '{email}', '{domain}', {time}, 'LogX');

-- Database Structure End --

*/
