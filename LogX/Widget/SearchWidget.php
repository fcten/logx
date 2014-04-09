<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */
 
class SearchWidget extends Widget {

	/**
	 * @brief showSearch 显示搜索结果
	 *
	 * @param $params 传入参数
	 *
	 * @return void
	 */
	public function showSearch( $params ) {
		if( !isset( $params['word'] ) ) {
			if( !( $word = Request::P('word','string') ) ) {
				Response::back();
			} else {
				Response::redirect(  Router::patch( 'SearchWord', array( 'word' => urlencode( trim( $word ) ) ) ) );
			}
			return;
		}

		// 获取文章数据
		Widget::initWidget('Post');
		Widget::getWidget('Post')->setPerPage( 8 );
		Widget::getWidget('Post')->setCurrentPage( isset($params['page'])?$params['page']:1 );
		// 未来支持分类内搜索
		// Widget::getWidget('Post')->setCurrentMeta( $m[0]['mid'] ); 
		Widget::getWidget('Post')->setSearchWord( urldecode( trim( $params['word'] ) ) );
		Widget::getWidget('Post')->query();

		// 设置标题、描述、关键词
		Widget::getWidget('Global')->title = urldecode( $params['word'] );
		// Widget::getWidget('Global')->description = $m[0]['description'];
		Widget::getWidget('Global')->keywords = urldecode( $params['word'] );

		$this->display('index.php');
	}

}
