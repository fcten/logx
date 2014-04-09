<?php

class MemcacheCache {

	private $memcache;

	// 缓存类型
	private $type;

	// 缓存变量
	private $cache;

	// 缓存配置
	private $options;
	
	function __construct( $options = '' ) {
		$this->type = 'MEMCACHE';

		if ( !class_exists('Memcache') ) {
			throw new NovaException( L( '_CACHE_MEMCACHE_UNSUPPERT_' ), E_ERROR );
		}

		if( !defined('MEMCACHE_HOST') ) {
			throw new NovaException( L( '_CACHE_MEMCACHE_HOST_UNDEFINED_' ), E_ERROR );
		}
		//$this->memcache = memcache_init();
		$this->memcache = new Memcache();
		if( !$this->memcache->pconnect(MEMCACHE_HOST, 11211) ) {
			throw new NovaException( L( '_CACHE_MEMCACHE_UNCONNECT_' ), E_ERROR );
		}
	}

	function __destruct() {}

	// 增加或修改一个缓存变量
	public function set( $index, $value = '', $timeout = 0 ) {	
		$this->memcache->set( $index, $value, MEMCACHE_COMPRESSED, $timeout );
	}

	// 获取一个缓存变量
	public function get( $index ) {
		$temp = $this->memcache->get( $index );
		if( $temp == array() ) {
			//[DEBUG]
			if( defined( 'LOGX_DEBUG' ) ) {
				Log::add( '[' . __FILE__ . '] [' . __LINE__ . '] ' . L( '_USE_ILLEGAL_INDEX_' ) . ' ' . $index, E_USER_NOTICE );
			}
			//[/DEBUG]
			return NULL;
		}
		return $temp;
	}

	// 获取所有缓存
	// 尚未完成
	public function get_all() {
		return NULL;
	}

	// 刷新一条缓存
	// 参数 $p 为 TRUE 时 强制刷新
	public function refresh( $index, $p = FALSE ) {
		if( $p ) {
			$this->memcache->delete( $index );
		}
	}

	// 刷新整个缓存
	// 参数 $p 为 TRUE 时 强制刷新
	public function refresh_all( $p = FALSE ) {
		if( $p ) {
			$this->memcache->flush();
		}
	}

	// 删除一条缓存
	public function delete($index) {
		$this->memcache->delete( $index );
	}

	// 删除整个缓存
	public function delete_all($index) {
		$this->memcache->flush();
	}

}
?>
