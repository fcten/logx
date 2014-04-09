<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

class LogXException extends Exception {

	public function __toString() {
		Response::error( 'Exception', '[FILE] '.str_replace(LOGX_ROOT,'',$this->getFile()).'<br><br>[LINE] '.$this->getLine().'<br><br>[MSG] '.$this->getMessage() );
		return $this->getMessage();
	}

}

class LogXErrorException extends ErrorException {
	public function __toString() {
		Response::error( 'Error', '[FILE] '.str_replace(LOGX_ROOT,'',$this->getFile()).'<br><br>[LINE] '.$this->getLine().'<br><br>[MSG] '.$this->getMessage() );
		return $this->getMessage();
	}
}

?>
