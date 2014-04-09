<?php

class ShmopCache {

	private $handler;

	// 缓存类型
	private $type;

	public function __construct() {
		$this->type = 'SHMOP';

		if ( !extension_loaded('shmop') ) {
			throw new NovaException( L( '_CACHE_SHMOP_UNSUPPERT_' ), E_ERROR );
		}

		if( empty( $options ) ){
			$options = array(
				'size' => 1048576,  // 1M
				'tmp'  => LOGX_CACHE,
				'project' => 's',
			);
		}
		$this->options = $options;
		$this->handler = $this->_ftok( $this->options['project'] );

		// 初始化
		$this->_read();
	}

	function __destruct() {
		// 把改变写入共享缓存
		return $this->_write();
	}

	public function __set( $name, $value ) {
		$this->set( $name, $value, CACHE_TIMEOUT );
	}

	public function __get( $name ) {
		return $this->get( $name );
	}

	// 增加或修改一个缓存变量
	public function set( $index, $value = '', $timeout = 0 ) {
		//$this->_read();
		$this->cache[$index]['edit'] = TRUE;
		$this->cache[$index]['time'] = time();
		$this->cache[$index]['timeout'] = $timeout;
	   	$this->cache[$index]['value'] = $value;
		//$this->_write();
	}

	// 获取一个缓存变量
	public function get( $index ) {
		//$this->_read();
		if( !isset( $this->cache[$index] ) ) {
			//[DEBUG]
			if( defined( 'APP_DEBUG' ) && APP_DEBUG === TRUE ) {
				$this->log( '[' . __FILE__ . '] [' . __LINE__ . '] Use illegal index: ' . $index, E_USER_NOTICE );
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

	// 获取所有缓存
	public function get_all() {
		$this->refresh_all();
		return $this->cache;
	}

	// 刷新一条缓存
	// 参数 $p 为 TRUE 时 强制刷新
	public function refresh( $index, $p = FALSE ) {
		//$this->_read();
		if( !isset( $this->cache[$index] ) ) {
			//[DEBUG]
			if( defined( 'APP_DEBUG' ) && APP_DEBUG === TRUE ) {
				$this->log( '[' . __FILE__ . '] [' . __LINE__ . '] Use illegal index: ' . $index, E_USER_NOTICE );
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

	// 刷新整个缓存
	// 参数 $p 为 TRUE 时 强制刷新
	public function refresh_all( $p = FALSE ) {
		$keys = array_keys( $this->cache );
		foreach ( $keys as $index ) {
			$this->refresh( $index, $p );
		}
	}

	// 删除一条缓存
	public function delete($index) {
		//$this->_read();
		if( !isset( $this->cache[$index] ) ) {
			//[DEBUG]
			if( defined( 'APP_DEBUG' ) && APP_DEBUG === TRUE ) {
				$this->log( '[' . __FILE__ . '] [' . __LINE__ . '] Use illegal index: ' . $index, E_USER_NOTICE );
			}
			//[/DEBUG]
			return;
		}
		unset( $this->cache[$index] );
		//$this->_write();
	}

	// 删除整个缓存
	public function delete_all() {
		$this->cache = array();
		//$this->_write();
	}

	// 获取缓存方式
	public function get_type() {
		return $this->type;
	}

	// 获取配置信息
	public function get_options() {
		return $this->options;
	}

	private function _ftok($project) {
		if ( function_exists( 'ftok' ) ) {
			return ftok( __FILE__, $project );
		}
		if( strtoupper( PHP_OS ) == 'WINNT' ){
			$s = stat( __FILE__ );
			return sprintf( "%u", ( ( $s['ino'] & 0xffff ) | ( ( $s['dev'] & 0xff ) << 16 ) | ( ( $project & 0xff ) << 24 ) ) );
		} else {
			$filename = __FILE__ . (string) $project;
			for( $key = array(); sizeof( $key ) < strlen( $filename ); $key[] = ord( substr( $filename, sizeof( $key ), 1 ) ) );
			return dechex( array_sum( $key ) );
		}
	}

	private function _read() {
		$id = @shmop_open( $this->handler, 'a', 0600, $this->options['size'] );
		if( $id !== FALSE ){
			$this->cache = unserialize( shmop_read( $id, 0, shmop_size( $id ) ) );
			shmop_close($id);
		}
	}

	private function _write() {
		$val = serialize( $this->cache );
		$lh = $this->_lock();
		$id = shmop_open( $this->handler, 'c', 0600, $this->options['size'] );
		if ($id) {
			$ret = shmop_write( $id, $val, 0 ) == strlen( $val );
			shmop_close( $id );
			$this->_unlock($lh);
			return $ret;
		}
		$this->_unlock($lh);
		return false;
	}

	private function _lock() {
		if ( function_exists( 'sem_get' ) ) {
			$fp = sem_get( $this->handler, 1, 0600, 1 );
			sem_acquire ( $fp );
		} else {
			$fp = fopen( NOVA_CACHE . md5( $this->handler ) . '.txt', 'w' );
			flock( $fp, LOCK_EX );
		}
		return $fp;
	}

	private function _unlock( &$fp ) {
		if ( function_exists( 'sem_release' ) ) {
			sem_release( $fp );
		} else {
			fclose( $fp );
		}
	}

}

?>
