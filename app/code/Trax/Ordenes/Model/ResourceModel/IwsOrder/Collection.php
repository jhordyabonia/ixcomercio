<?php

namespace Trax\Ordenes\Model\ResourceModel\IwsOrder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialization.
     */
	public function _construct()
    {
		$this->_init(
            \Trax\Ordenes\Model\IwsOrder::class,
            \Trax\Ordenes\Model\ResourceModel\IwsOrder::class
        );
	}
}
