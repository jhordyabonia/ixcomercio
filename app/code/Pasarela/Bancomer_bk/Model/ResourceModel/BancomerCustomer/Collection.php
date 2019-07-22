<?php
namespace Pasarela\Bancomer\Model\ResourceModel\BancomerCustomer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'post_id';
    protected $_eventPrefix = 'mageplaza_helloworld_post_collection';
    protected $_eventObject = 'post_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Pasarela\Bancomer\Model\BancomerCustomer', 'Pasarela\Bancomer\Model\ResourceModel\BancomerCustomer');
    }

}
