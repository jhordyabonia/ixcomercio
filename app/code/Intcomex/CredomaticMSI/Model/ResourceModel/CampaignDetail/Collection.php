<?php

namespace Intcomex\CredomaticMSI\Model\ResourceModel\CampaignDetail;

use Intcomex\CredomaticMSI\Model\CampaignDetail;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Intcomex\CredomaticMSI\Model\ResourceModel\CampaignDetail as ResourceCampaignDetail;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    public function _construct()
    {
        $this->_init(CampaignDetail::class, ResourceCampaignDetail::class);
    }

    public function getDetailsSku($sku, $campaign_id)
    {
        $this->getSelect()
            ->where("sku = ". "'$sku'")
            ->where('campaign_id = '.$campaign_id);
        return $this;
    }
}
