<?php

namespace Intcomex\CredomaticMSI\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CampaignDetail extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('campaign_detail', 'id');
    }
}
