<?php

namespace Intcomex\Xtento\Model;

class Xtxml extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	protected function _construct()
	{
		$this->_init('Intcomex\Xtento\Model\ResourceModel\Xtxml');
	}
}