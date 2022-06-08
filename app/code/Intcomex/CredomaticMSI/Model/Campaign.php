<?php

namespace Intcomex\CredomaticMSI\Model;

use Magento\Framework\Model\AbstractModel;

class Campaign extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Intcomex\CredomaticMSI\Model\ResourceModel\Campaign::class);
    }
}
