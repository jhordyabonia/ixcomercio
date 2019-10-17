<?php 
namespace Trax\Places\Model\ResourceModel\TraxPlacesRegions;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Trax\Places\Model\TraxPlacesRegions","Trax\Places\Model\ResourceModel\TraxPlacesRegions");
	}
}