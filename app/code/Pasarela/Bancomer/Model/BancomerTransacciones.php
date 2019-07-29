<?php 
namespace Pasarela\Bancomer\Model;

class BancomerTransacciones extends \Magento\Framework\Model\AbstractModel{
	public function _construct(){
		$this->_init("Pasarela\Bancomer\Model\ResourceModel\BancomerTransacciones");
	}
}
