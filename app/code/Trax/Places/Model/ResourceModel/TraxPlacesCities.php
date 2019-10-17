<?php 
namespace Trax\Places\Model\ResourceModel;

class TraxPlacesCities extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
	public function _construct(){
		$this->_init("trax_places_cities","id");
	}
}
