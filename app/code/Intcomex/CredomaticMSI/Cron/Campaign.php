<?php
 
namespace Intcomex\CredomaticMSI\Cron;
use Intcomex\CredomaticMSI\Model\ResourceModel\Campaign\CollectionFactory;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use Intcomex\CredomaticMSI\Helper\UpdateFeeAttributeHelper;
class Campaign
{
    /**
    * @var CollectionFactory
    */
    private $_collection;

    /** 
    * @var UpdateFeeAttributeHelper
    */
    private $updateFeeAttributeHelper;

    /**
    * @var DateTime
    */
    private $date;

    public function __construct(
        CollectionFactory $collection,
        DateTime $date,
        UpdateFeeAttributeHelper $updateFeeAttributeHelper
    )
    {
        $this->_collection = $collection;
        $this->date = $date;
        $this->updateFeeAttributeHelper = $updateFeeAttributeHelper;
    }

    public function execute()
    {
        $currentTime = $this->date->gmtDate();
        $currentTime = date($currentTime);
        $currentTime = strtotime ( '-6 hour' , strtotime ($currentTime));
        $currentTime = date ( 'Y-m-d H:i:s' , $currentTime); 
        $expiredCampaigns = $this->_collection->create()->getExpiredCampaigns($currentTime)->getData();

        foreach ($expiredCampaigns as $campaign) {
            $this->_collection->create()->disableCampaigns($campaign['id'])->getData();
            $this->_collection->create()->disableCampaignsDetails($campaign['id'])->getData();
            $expiredCampaignDetails = $this->_collection->create()->getDetailsCampaign($campaign['id'])->getData();
            foreach ($expiredCampaignDetails as $campaignDetail) {
                $this->updateFeeAttributeHelper->delete($campaignDetail);
            }
        }
    }
}