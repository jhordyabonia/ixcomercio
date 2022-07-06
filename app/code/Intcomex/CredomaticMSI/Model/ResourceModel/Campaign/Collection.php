<?php

namespace Intcomex\CredomaticMSI\Model\ResourceModel\Campaign;

use Intcomex\CredomaticMSI\Model\Campaign;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Intcomex\CredomaticMSI\Model\ResourceModel\Campaign as ResourceCampaign;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(Campaign::class, ResourceCampaign::class);
    }

    public function getDetailsCampaignActive()
    {
        $joinConditions = 'main_table.id = campaign_detail.campaign_id';
        $this->getSelect()
            ->join(
                ['campaign_detail'],
                $joinConditions,
                [
                    'campaign_detail.sku',
                    'campaign_detail.fee',
                    'campaign_detail.max_units'
                ]
            )->where('main_table.status = 1')
            ->where('campaign_detail.status = 1');
        return $this;
    }

    public function getExpiredCampaigns($date)
    {
        $this->getSelect()
            ->where('end_date <= ?', $date)
            ->where('status = 1');
        return $this;
    }

    public function disableCampaigns($campaignId)
    {
        $connection = $this->getConnection();
        $dataUpdate = ['status' => 0];
        $where = ['id=?' => $campaignId];
        $connection->update($this->getTable('campaign'), $dataUpdate, $where);
        return $this;
    }

    public function disableCampaignsDetails($campaignId)
    {
        $connection = $this->getConnection();
        $dataUpdate = ['status' => 0];
        $whereDetail = ['campaign_id=?' => $campaignId];
        $connection->update($this->getTable('campaign_detail'), $dataUpdate, $whereDetail);
        return $this;
    }

    public function getDetailsCampaign($campaignId)
    {
        $joinConditions = 'main_table.id = campaign_detail.campaign_id';
        $this->getSelect()
            ->join(
                ['campaign_detail'],
                $joinConditions,
                [
                    'campaign_detail.sku',
                    'campaign_detail.fee',
                    'campaign_detail.status'
                ]
            )->where('main_table.id ='. $campaignId);
        return $this;
    }
}