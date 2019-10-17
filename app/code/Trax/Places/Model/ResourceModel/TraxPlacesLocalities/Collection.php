<?php 
namespace Trax\Places\Model\ResourceModel\TraxPlacesLocalities;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Trax\Places\Model\TraxPlacesLocalities","Trax\Places\Model\ResourceModel\TraxPlacesLocalities");
	}
}