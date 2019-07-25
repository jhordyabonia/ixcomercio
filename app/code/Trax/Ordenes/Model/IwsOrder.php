<?php 
namespace Trax\Ordenes\Model;

class IwsOrder extends \Magento\Framework\Model\AbstractModel{
	public function _construct(){
		$this->_init("Trax\Ordenes\Model\ResourceModel\IwsOrder");
	}
}
