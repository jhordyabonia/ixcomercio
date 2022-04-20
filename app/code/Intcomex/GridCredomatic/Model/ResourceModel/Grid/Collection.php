<?php


namespace Intcomex\GridCredomatic\Model\ResourceModel\Grid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            'Intcomex\GridCredomatic\Model\Grid',
            'Intcomex\GridCredomatic\Model\ResourceModel\Grid'
        );
    }
}
