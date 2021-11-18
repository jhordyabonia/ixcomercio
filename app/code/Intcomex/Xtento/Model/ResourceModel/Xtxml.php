<?php

namespace Intcomex\Xtento\Model\ResourceModel;

class Xtxml extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('xtento_catalog_xml', 'id');
	}
	
}