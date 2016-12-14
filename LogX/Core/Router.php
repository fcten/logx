<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class Router {

	// 路由表
	private static $_routeTable = array();

	// 当前匹配的路由
	private static $_currentRoute = '';

	// 当前匹配的路由参数
	private static $_currentParams = array();

	/**
	 * @brief init 初始化路由表
	 *
	 * @return void
	 */
	public static function init() {
		$route = array(
			'widget' => 'Index',
			'method' => 'showIndex',
			'format' => '/',
			'patter' => '|^[/]?$|',
			'params' => array()
			);
		self::setRoute( 'Index', $route );
		$route = array(
			'widget' => 'Index',
			'method' => 'showIndex',
			'format' => '/page/%d/',
			'patter' => '|^/page/([0-9]+)[/]?$|',
			'params' => array('page')
			);
		self::setRoute( 'IndexPage', $route );
		$route = array(
			'widget' => 'Post',
			'method' => 'showPost',
			'format' => '/post/%d/',
			'patter' => '|^/post/([0-9]+)[/]?$|',
			'params' => array('pid')
			);
		self::setRoute( 'Post', $route );
		$route = array(
			'widget' => 'Meta',
			'method' => 'showCategory',
			'format' => '/category/%s/',
			'patter' => '|^/category/([^/]+)[/]?$|',
			'params' => array('alias')
			);
		self::setRoute( 'Category', $route );
		$route = array(
			'widget' => 'Meta',
			'method' => 'showCategory',
			'format' => '/category/%s/%d/',
			'patter' => '|^/category/([^/]+)/([0-9]+)[/]?$|',
			'params' => array('alias','page')
			);
		self::setRoute( 'CategoryPage', $route );
		$route = array(
			'widget' => 'Meta',
			'method' => 'showTag',
			'format' => '/tags/%s/',
			'patter' => '|^/tags/([^/]+)[/]?$|',
			'params' => array('name')
			);
		self::setRoute( 'Tag', $route );
		$route = array(
			'widget' => 'Meta',
			'method' => 'showTag',
			'format' => '/tag/%s/%d/',
			'patter' => '|^/tag/([^/]+)/([0-9]+)[/]?$|',
			'params' => array('name','page')
			);
		self::setRoute( 'TagPage', $route );
		$route = array(
			'widget' => 'Meta',
			'method' => 'showAttachment',
			'format' => '/attachment/%d/',
			'patter' => '|^/attachment/([0-9]+)[/]?$|',
			'params' => array('mid')
			);
		self::setRoute( 'Attachment', $route );
		$route = array(
			'widget' => 'Search',
			'method' => 'showSearch',
			'format' => '/search/',
			'patter' => '|^/search[/]?$|',
			'params' => array()
		);
		Router::setRoute('Search',$route);
		$route = array(
			'widget' => 'Search',
			'method' => 'showSearch',
			'format' => '/search/%s/',
			'patter' => '|^/search/([^/]+)[/]?$|',
			'params' => array('word')
		);
		Router::setRoute('SearchWord',$route);
		$route = array(
			'widget' => 'Search',
			'method' => 'showSearch',
			'format' => '/search/%s/%d/',
			'patter' => '|^/search/([^/]+)/([0-9]+)[/]?$|',
			'params' => array('word','page')
		);
		Router::setRoute('SearchPage',$route);
		$route = array(
			'widget' => 'User',
			'method' => 'showUser',
			'format' => '/author/%d/',
			'patter' => '|^/author/([0-9]+)[/]?$|',
			'params' => array('uid')
		);
		self::setRoute( 'Author', $route );
		$route = array(
			'widget' => 'User',
			'method' => 'showUser',
			'format' => '/author/%d/%d/',
			'patter' => '|^/author/([0-9]+)/([0-9]+)[/]?$|',
			'params' => array('uid','page')
		);
		self::setRoute( 'AuthorPage', $route );
		$route = array(
			'widget' => 'Admin',
			'method' => 'showAdmin',
			'format' => '/admin/',
			'patter' => '|^/admin[/]?$|',
			'params' => array()
			);
		self::setRoute( 'Admin', $route );
		$route = array(
			'widget' => 'Admin',
			'method' => 'doAdmin',
			'format' => '/admin/%s/',
			'patter' => '|^/admin/([^/]+)[/]?$|',
			'params' => array('do')
			);
		self::setRoute( 'AdminDo', $route );
		$route = array(
			'widget' => 'Action',
			'method' => 'doAction',
			'format' => '/action/%s/',
			'patter' => '|^/action/([_0-9a-zA-Z-]+)[/]?$|',
			'params' => array('do')
			);
		self::setRoute( 'Action', $route );
	}

	/**
	 * @brief dispatch 路由分发方法
	 *
	 * @return void
	 */
	public static function dispatch() {
		// 注册最后的通用路由
		$route = array(
			'widget' => 'Page',
			'method' => 'showPage',
			'format' => '/%s/',
			'patter' => '|^/([^/]+)[/]?$|',
			'params' => array('alias')
			);
		self::setRoute( 'Page', $route );

		// 获取地址信息
		if( OptionLibrary::get('rewrite') == 'open' ) {
			$pathInfo = str_replace( substr( LOGX_PATH, 0, strlen( LOGX_PATH )-1 ), '', Request::S( 'REQUEST_URI', 'string' ) );
			$pathInfo = str_replace( '?'.Request::S( 'QUERY_STRING', 'string' ), '', $pathInfo );
		} else {
			$pathInfo = self::getPathInfo();
		}
		$pathInfo = Plugin::call( 'pathInfo', $pathInfo );

		// 遍历路由表进行匹配
		foreach( self::$_routeTable as $key => $route ) {
			if( preg_match( $route['patter'], $pathInfo, $matches ) ) {
				self::$_currentRoute = $key;
				$params = NULL;
				if( !empty($route['params']) ) {
					unset($matches[0]);
					$params = array_combine($route['params'], $matches);
				}
				self::$_currentParams = $params;
				if( isset( $route['widget'] ) ) {
					Widget::getWidget( $route['widget'] )->{$route['method']}( $params );
				} elseif( isset( $route['plugin'] ) ) {
					Plugin::getPlugin( $route['plugin'] )->{$route['method']}( $params );
				}
				return;
			}
		}
		//echo '**'.$_SERVER['QUERY_STRING'];
		//$path = explode( '/', $pathInfo );

		// 永久重定向为规范的 URL 地址
		//Response::redirect( $pathInfo.'/', true );

		// 没有匹配的路由则显示 404 页面
		Response::error(404);
	}

	/**
	 * @brief patch 路由组装
	 *
	 * @param $routeName 路由名
	 * @param $params 路由规则参数
	 *
	 * @return string
	 */
	public static function patch( $routeName, $params ) {
		if( $route = self::getRoute( $routeName ) ) {
			$pattern = array();
			foreach ($route['params'] as $row) {
				$pattern[$row] = isset($params[$row]) ? $params[$row] : '{' . $row . '}';
			}
			if( OptionLibrary::get('rewrite') == 'open' ) {
				return Request::getDomain() . substr( LOGX_PATH, 0, strlen( LOGX_PATH )-1 ) . vsprintf($route['format'], $pattern);
			} else {
				return Request::getDomain() . LOGX_PATH . 'index.php' . vsprintf($route['format'], $pattern);
			}
		} else {
			return '';
		}
	}

	/**
	 * @brief setRoute 设置路由规则
	 *
	 * @param $routeName 路由名
	 * @param $route 路由规则
	 *
	 * @return void
	 */
	public static function setRoute( $routeName, $route ) {
		if( isset( self::$_routeTable[$routeName] ) ) {
			self::$_routeTable[$routeName] = $route;
		} else {
			self::$_routeTable[$routeName] = $route;
		}
	}

	/**
	 * @brief getRoute 获取路由规则
	 *
	 * @param $routeName 路由名
	 *
	 * @return array
	 */
	public static function getRoute( $routeName ) {
		if( isset( self::$_routeTable[$routeName] ) ) {
			return self::$_routeTable[$routeName];
		} else {
			return NULL;
		}
	}

	/**
	 * @brief getAllRoutes 获取全部路由规则
	 *
	 * @return array
	 */
	public static function getAllRoutes() {
		return self::$_routeTable;
	}

	/**
	 * @brief getCurrentRoute 获取当前被匹配的路由规则
	 *
	 * @return string
	 */
	public static function getCurrentRoute() {
		return self::$_currentRoute;
	}

	/**
	 * @brief getCurrentParams 获取当前被匹配的路由规则
	 *
	 * @return array
	 */
	public static function getCurrentParams() {
		return self::$_currentParams;
	}

	/**
	 * @brief getPathInfo 获取 PATH_INFO
	 *
	 * @return string
	 */
	public static function getPathInfo() {
		if( !empty( $_SERVER['ORIG_PATH_INFO'] ) ) {
			$pathInfo = $_SERVER['ORIG_PATH_INFO'];
		} elseif( !empty( $_SERVER['PATH_INFO'] ) ) {
			$pathInfo = $_SERVER['PATH_INFO'];
		} else {
			$pathInfo = '/';
		}
		if( 0 === strpos( $pathInfo, $_SERVER['SCRIPT_NAME'] ) ) {
			$path = substr( $pathInfo, strlen( $_SERVER['SCRIPT_NAME'] ) );
		} else {
			$path = $pathInfo;
		}
		return $path;
	}

}
