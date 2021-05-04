<?php
namespace Xtento\XtentoXtcore\Model\ResourceModel\Xtxml;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Xtento\XtentoXtcore\Model\Xtxml', 'Xtento\XtentoXtcore\Model\ResourceModel\Xtxml');
	}

}
