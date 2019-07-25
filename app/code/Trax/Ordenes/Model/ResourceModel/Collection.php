<?php 
namespace Trax\Ordenes\Model\ResourceModel\IwsOrder;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Trax\Ordenes\Model\IwsOrder","Trax\Ordenes\Model\ResourceModel\IwsOrder");
	}
}