<?php 
namespace Pasarela\Bancomer\Model\ResourceModel;

class BancomerTransacciones extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
	public function _construct(){
		$this->_init("bancomer_transacciones","id");
	}
}
