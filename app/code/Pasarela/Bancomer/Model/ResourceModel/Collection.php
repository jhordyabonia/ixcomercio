<?php 
namespace Pasarela\Bancomer\Model\ResourceModel\BancomerTransacciones;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Pasarela\Bancomer\Model\BancomerTransacciones","Pasarela\Bancomer\Model\ResourceModel\BancomerTransacciones");
	}
}