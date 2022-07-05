<?php

namespace Intcomex\CredomaticMSI\Model;

use Magento\Framework\Model\AbstractModel;

class CampaignDetail extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Intcomex\CredomaticMSI\Model\ResourceModel\CampaignDetail::class);
    }
}
