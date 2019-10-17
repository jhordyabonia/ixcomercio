<?php 
namespace Trax\Places\Model\ResourceModel\TraxPlacesCities;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Trax\Places\Model\TraxPlacesCities","Trax\Places\Model\ResourceModel\TraxPlacesCities");
	}
}