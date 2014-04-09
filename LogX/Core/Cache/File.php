<?php

class FileCache {

	// 缓存类型
	private $type;

	// 缓存变量
	private $cache;

	// 缓存配置
	private $options;

	/**
	 * @brief __construct 构造函数
	 *
	 * @param $options 配置信息
	 *
	 * @return void
	 */
	function __construct( $options = '' ) {
		if( !defined( 'CACHE_TIMEOUT' ) ) {
			defined( 'CACHE_TIMEOUT', 60 );
		} else {
			if( !is_integer( CACHE_TIMEOUT ) || CACHE_TIMEOUT < 0 ) {
				throw new LogXException( _t( 'Cache timeout is an illegal number.' ), E_ERROR );
			}
		}

		$this->cache = array();
		$this->type = 'FILE';

		if( !defined( 'LOGX_CACHE' ) || !is_dir( LOGX_CACHE ) ) {
			throw new LogXException( _t( 'Cache config is illegal.' ), E_ERROR );
		}

		$files = Logx::readFile( LOGX_CACHE, 'php' );
		foreach( $files as $file ) {
			if( substr( $file, -10 ) == '.cache.php' ) {
				@include_once $file;
			}
		}
	}

	/**
	 * @brief __destruct 析构函数
	 *
	 * @return void
	 */
	function __destruct() {
		// 把改变写入文件
		$keys = array_keys( $this->cache );
		foreach ( $keys as $index ) {
			if( $this->cache[$index]['edit'] === TRUE ) {
				$this->cache[$index]['edit'] = FALSE;
				$this->write_to_file( $index );
			}
		}
	}

	/**
	 * @brief set 增加或修改一个缓存变量
	 *
	 * @param $index 变量名
	 * @param $value 变量值
	 * @param $timeout 缓存超时过期时间
	 *
	 * @return void
	 */
	public function set( $index, $value = '', $timeout = 0 ) {
		$this->cache[$index]['edit'] = TRUE;
		$this->cache[$index]['time'] = time();
		$this->cache[$index]['timeout'] = $timeout;
	   	$this->cache[$index]['value'] = $value;
	}

	/**
	 * @brief get 获取一个缓存变量
	 *
	 * @param $index 变量值
	 *
	 * @return mix
	 */
	public function get( $index ) {
		//$this->_read();
		if( !isset( $this->cache[$index] ) ) {
			//[DEBUG]
			if( defined( 'LOGX_DEBUG' ) ) {
				Log::add( '[' . __FILE__ . '] [' . __LINE__ . '] Use illegal index: ' . $index, E_USER_NOTICE );
			}
			//[/DEBUG]
			return NULL;
		}
		$this->refresh( $index );
		if( isset( $this->cache[$index]['value'] ) ) {
			return $this->cache[$index]['value'];
		} else {
			return NULL;
		}
	}

	/**
	 * @brief getAll 获取所有缓存
	 *
	 * @return array
	 */
	public function getAll() {
		$this->refreshAll();
		return $this->cache;
	}

	/**
	 * @brief refresh 刷新一条缓存
	 *
	 * @param $index 变量名
	 * @param $p 是否强制刷新
	 *
	 * @return void
	 */
	public function refresh( $index, $p = FALSE ) {
		//$this->_read();
		if( !isset( $this->cache[$index] ) ) {
			//[DEBUG]
			if( defined( 'LOGX_DEBUG' ) ) {
				Log::add( '[' . __FILE__ . '] [' . __LINE__ . '] Use illegal index: ' . $index, E_USER_NOTICE );
			}
			//[/DEBUG]
			return;
		}
		if( ( $this->cache[$index]['timeout'] > 0 && ( $this->cache[$index]['time'] + $this->cache[$index]['timeout'] ) <= time() ) || $p ) {
			$this->cache[$index]['edit'] = TRUE;
			$this->cache[$index]['time'] = time();
	   		$this->cache[$index]['value'] = NULL;
		}
		//$this->_write();
	}

	/**
	 * @brief refreshAll 刷新整个缓存
	 *
	 * @param $p 是否强制刷新
	 *
	 * @return void
	 */
	public function refreshAll( $p = FALSE ) {
		$keys = array_keys( $this->cache );
		foreach ( $keys as $index ) {
			$this->refresh( $index, $p );
		}
	}

	/**
	 * @brief delete 删除一条缓存
	 *
	 * @param $index 变量名
	 *
	 * @return void
	 */
	public function delete($index) {
		//$this->_read();
		if( !isset( $this->cache[$index] ) ) {
			//[DEBUG]
			if( defined( 'LOGX_DEBUG' ) ) {
				Log::add( '[' . __FILE__ . '] [' . __LINE__ . '] Use illegal index: ' . $index, E_USER_NOTICE );
			}
			//[/DEBUG]
			return;
		}
		unset( $this->cache[$index] );
		@unlink( LOGX_CACHE . $index . '.cache.php' );
		//$this->_write();
	}

	/**
	 * @brief deleteAll 删除整个缓存
	 *
	 * @return void
	 */
	public function deleteAll() {
		$this->cache = array();

		$files = LogX::readFile( LOGX_CACHE, 'php' );
		foreach( $files as $file ) {
			if( substr( $file, -10 ) == '.cache.php' ) {
				@unlink( $file );
			}
		}
	}

	/**
	 * @brief getType 获取缓存方式
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @brief getOptions 获取配置信息
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @brief write_to_file 生成缓存文件
	 *
	 * @param $name 变量名
	 *
	 * @return int
	 */
	private function write_to_file( $name ) {
		$content = "\$this->cache['$name'] = " . var_export( $this->cache[$name], True ) . ';';
		$content = "<?php\n\nif( !defined( 'LOGX_ROOT' ) ) die('Access denied!');\n\n// 该文件是系统自动生成的缓存文件，请勿修改\n// 创建时间：" . date( 'Y-m-d H:i:s', time() ) . "\n\n" . $content . "\n\n?>";
		$file_name = LOGX_CACHE . $name . '.cache.php';
		if( !( $len = @file_put_contents( $file_name, $content ) ) ) {
			throw new LogXException( _t('Cache directory cannot write.') );
		}
		@chmod( $file_name, 0777 );
		return $len;
	}

}
?>
