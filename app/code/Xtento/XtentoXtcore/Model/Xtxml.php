<?php

namespace Xtento\XtentoXtcore\Model;

class Xtxml extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	protected function _construct()
	{
		$this->_init('Xtento\XtentoXtcore\Model\ResourceModel\Xtxml');
	}
}