<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

/**
 * @brief 特别说明，此处的 Widget 有不同含义。为了避免混淆，我把它释义为“小工具”。
 *        这是为了增强主题设计的灵活性而设置的。请不要与 LogX 的“组件”概念相混淆。
 */
class WidgetWidget extends Widget {

	// 主题中可用的小工具信息
	private $_widgets;

	/**
	 * @brief init 初始化小工具组件
	 *
	 * @return void
	 */
	public function init() {
		$this->_widgets = array();
	}

	/**
	 * @brief set 设置小工具
	 *
	 * @param $name 小工具名称 
	 * @param $function 注册的函数
	 *
	 * @return void
	 */
	public function set( $name, $function ) {
		// 这里不判断小工具是否已经存在，也就是说后
		// 注册的小工具可以覆盖掉先注册的小工具
		$this->_widgets[$name] = $function;
	}

	/**
	 * @brief widgetGet 获取注册的小工具信息
	 *
	 * @param $name 小工具名
	 *
	 * @return array
	 */
	public function widgetGet( $name ) {
		if( isset( $this->_widgets[$name] ) ) {
			return $this->_widgets[$name];
		} else {
			return NULL;
		}
	}

	/**
	 * @brief widgetGetAll 获取所有可用的小工具
	 *
	 * @return array
	 */
	public function widgetGetAll() {
		// 根据设定予以排序
		$sort = array();
		foreach( $this->_widgets as $key => $widget ) {
			$sort[$key] = isset( $widget['sort'] ) ? $widget['sort'] : 0;
		}
		array_multisort($sort, SORT_DESC, $this->_widgets);
		return $this->_widgets;
	}

	/**
	 * @brief widgetName 输出小工具名
	 *
	 * @param $widget 小工具对象
	 *
	 * @return void
	 */
	public function widgetName( $widget ) {
		echo $widget['name'];
	}

	/**
	 * @brief widgetContent 输出小工具内容
	 *
	 * @param $widget 小工具对象
	 * @param $format 输出格式
	 *
	 * @return void
	 */
	public function widgetContent( $widget, $format = '' ) {
		echo Plugin::call( $widget['call'], $format );
	}

}

function widget_get( $name ) {
	return Widget::getWidget('Widget')->widgetGet( $name );
}

function widget_get_all() {
	return Widget::getWidget('Widget')->widgetGetAll();
}

function widget_name( $widget ) {
	return Widget::getWidget('Widget')->widgetName( $widget );
}

function widget_content( $widget, $format = '' ) {
	return Widget::getWidget('Widget')->widgetContent( $widget, $format );
}
