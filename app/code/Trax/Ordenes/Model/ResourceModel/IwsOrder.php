<?php 
namespace Trax\Ordenes\Model\ResourceModel;

class IwsOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
	public function _construct(){
		$this->_init("iws_order","id");
	}
}
