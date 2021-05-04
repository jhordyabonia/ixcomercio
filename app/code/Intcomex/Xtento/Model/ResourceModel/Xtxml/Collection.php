<?php
namespace Intcomex\Xtento\Model\ResourceModel\Xtxml;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Intcomex\Xtento\Model\Xtxml', 'Intcomex\Xtento\Model\ResourceModel\Xtxml');
	}

}
