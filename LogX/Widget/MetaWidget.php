<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class MetaWidget extends Widget {

	/**
	 * @brief showCategory 显示某分类下的文章
	 *
	 * @param $params 参数
	 *
	 * @return void
	 */
	public function showCategory( $params ) {
		// 根据 Meta 别名获取 Meta ID
		$meta = new MetaLibrary();
		$meta->setType( 1 );
		$meta->setAlias( $params['alias'] );
		if( !( $m = $meta->getMeta() ) ) {
			Response::error( 404 );
			return;
		}

		// 获取文章数据
		Widget::initWidget('Post');
		Widget::getWidget('Post')->setPerPage( 8 );
		Widget::getWidget('Post')->setCurrentPage( isset($params['page'])?$params['page']:1 );
		Widget::getWidget('Post')->setCurrentMeta( $m[0]['mid'] );
		Widget::getWidget('Post')->query();

		// 设置标题、描述、关键词
		Widget::getWidget('Global')->title = urldecode( $m[0]['name'] );
		Widget::getWidget('Global')->description = $m[0]['description'];
		Widget::getWidget('Global')->keywords = urldecode( $m[0]['name'] );

		$this->display('index.php');
	}

	/**
	 * @brief showTag 显示某标签下的文章
	 *
	 * @param $params 参数
	 *
	 * @return void
	 */
	public function showTag( $params ) {
		// 根据 Meta 别名获取 Meta ID
		$meta = new MetaLibrary();
		$meta->setType( 2 );
		$meta->setName( urldecode( $params['name'] ) );
		if( !( $m = $meta->getMeta() ) ) {
			Response::error( 404 );
			return;
		}

		// 获取文章数据
		Widget::initWidget('Post');
		Widget::getWidget('Post')->setPerPage( 8 );
		Widget::getWidget('Post')->setCurrentPage( isset($params['page'])?$params['page']:1 );
		Widget::getWidget('Post')->setCurrentMeta( $m[0]['mid'] );
		Widget::getWidget('Post')->query();

		// 设置标题、描述、关键词
		Widget::getWidget('Global')->title = $m[0]['name'];
		Widget::getWidget('Global')->description = $m[0]['description'];
		Widget::getWidget('Global')->keywords = $m[0]['name'];

		$this->display('index.php');
	}

	/**
	 * @brief showAttachment 防盗链显示、下载附件
	 *
	 * @param $params 参数
	 *
	 * @return void
	 */
	public function showAttachment( $params ) {
		$meta = new MetaLibrary();
		$meta->setType( 3 );
		$meta->setMID( $params['mid'] );
		if( !( $m = $meta->getMeta() ) ) {
			Response::error( 404 );
			return;
		}
		$m = $m[0];

		// 判断 referer 防盗链
		$referer = Request::S( 'HTTP_REFERER', 'string' );
		if( $referer ) {
			$referer = parse_url( $referer );
			$host = parse_url( OptionLibrary::get( 'domain' ) );
			if ( LogX::getDomain( $referer['host'] ) !=  LogX::getDomain( $host['host'] ) ) {
				Response::error( 403 );
				exit;
			}
		}

		$m['alias'] = LOGX_FILE . $m['alias'];
		// 通过判断getimagesize取出的图片信息是否存在类型标记和色彩位深来防止伪造。
		$isimage = false;
		if ( stristr( $m['description'], 'image' ) ) {
			if( function_exists( 'getimagesize' ) ) {
				$imginfo = @getimagesize( $m['alias'] );
				if( isset( $imginfo[2] ) && isset( $imginfo['bits'] ) ) {
					$isimage = true;
				}
				unset( $imginfo );
			} else {
				$isimage = true;
			}
		}
		// 附件读取形式，inline直接读取，attachment下载到本地
		$disposition = $isimage ? 'inline' : 'attachment';
		// 统计附件下载次数
		if ( $disposition == 'attachment' ) {
			$meta->incReply( $params['mid'] );
		}

		$m['description'] = $m['description'] ? $m['description'] : 'application/octet-stream';
		if ( is_readable( $m['alias'] ) ) {
			@ob_end_clean();
			if ( $disposition == 'inline' ) {
				Response::setExpire( 60 * 24 * 365 );
			}
			header( 'content-Encoding: none' );
			header( 'content-type: ' . $m['description'] );
			header( 'content-Disposition: ' . $disposition . '; filename=' . urlencode( $m['name'] ) );
			header( 'content-Length: ' . abs( filesize( $m['alias'] ) ) );
			$fp = @fopen( $m['alias'], 'rb' ); 
			@fpassthru($fp);
			@fclose($fp);
			exit;		
		} else {
			Response::error( 404 );
		}
	}

	/**
	 * @brief addMeta 添加 Meta
	 *
	 * @param $type Meta 类型
	 *
	 * @return void
	 */
	public function addMeta( $type ) {
		$m['name'] = Request::P('name','string');
		$m['description'] = Request::P('description','string');
		$m['alias'] = Request::P('alias','string');
		$m['parent'] = Request::P('parent');
		$m['type'] = $type;

		$meta = new MetaLibrary();
		if( $meta->addMeta( $m ) ) {
			$r = array(
				'success' => TRUE,
				'message' => _t('Add Meta complete.')
			);
		} else {
			$r = array(
				'success' => FALSE,
				'message' => _t('Add Meta failed.')
			);
		}
		Response::ajaxReturn( $r );
	}

	/**
	 * @brief editMeta 编辑 Meta
	 *
	 * @param $type Meta 类型
	 *
	 * @return void
	 */
	public function editMeta( $type ) {
		$m['mid'] = Request::P('mid');
		$m['name'] = Request::P('name','string');
		$m['description'] = Request::P('description','string');
		$m['alias'] = Request::P('alias','string');
		$m['parent'] = Request::P('parent');
		$m['type'] = $type;

		$meta = new MetaLibrary();
		if( $meta->editMeta( $m ) ) {
			$r = array(
				'success' => TRUE,
				'message' => _t('Edit Meta complete.')
			);
		} else {
			$r = array(
				'success' => FALSE,
				'message' => _t('Edit Meta failed.')
			);
		}
		Response::ajaxReturn( $r );
	}

	/**
	 * @brief delMeta 删除 Meta
	 *
	 * @return void
	 */
	public function delMeta() {
		$mid = Request::P('mid');

		$meta = new MetaLibrary();
		if( $meta->delMeta( $mid ) ) {
			$r = array(
				'success' => TRUE,
				'message' => _t('Delete Meta complete.')
			);
		} else {
			$r = array(
				'success' => FALSE,
				'message' => _t('Delete Meta failed.')
			);
		}
		Response::ajaxReturn( $r );
	}

}
