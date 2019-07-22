<?php

namespace Pasarela\Bancomer\Model\ResourceModel;

class BancomerCustomer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context) {
        parent::__construct($context);
    }

    protected function _construct() {
        $this->_init('bancomer_customers', 'bancomer_customer_id');
    }
}
