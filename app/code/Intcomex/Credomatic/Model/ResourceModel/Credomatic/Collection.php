<?php
 
namespace Intcomex\Credomatic\Model\ResourceModel\Credomatic;
 
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection

{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Intcomex\Credomatic\Model\Credomatic',
            'Intcomex\Credomatic\Model\ResourceModel\Credomatic'
        );
    }
}