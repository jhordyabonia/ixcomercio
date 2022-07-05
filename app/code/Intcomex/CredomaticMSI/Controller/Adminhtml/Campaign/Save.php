<?php

namespace Intcomex\CredomaticMSI\Controller\Adminhtml\Campaign;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Intcomex\CredomaticMSI\Model\CampaignFactory;
use Intcomex\CredomaticMSI\Helper\UpdateFeeAttributeHelper;
use Intcomex\CredomaticMSI\Model\ResourceModel\Campaign\CollectionFactory;

class Save extends Action
{
    private $_collection;
    private $campaignFactory;
    private $updateFeeAttributeHelper;

    public function __construct(
        Context $context,
        CollectionFactory $collection,
        CampaignFactory $campaignFactory,
        UpdateFeeAttributeHelper $updateFeeAttributeHelper
    )
    {
        parent::__construct($context);
        $this->_collection = $collection;
        $this->campaignFactory = $campaignFactory;
        $this->updateFeeAttributeHelper = $updateFeeAttributeHelper;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('credomatic/campaign/addrow');
            return;
        }
        try {
            $rowData = $this->campaignFactory->create();
            $rowData->setData($data);
            if (isset($data['id'])) {
                $rowData->setEntityId($data['id']);
                $getDetailsCampaign = $this->_collection->create()->getDetailsCampaign($data['id'])->getData();
                if ($data['status'] == 1) {
                    foreach ($getDetailsCampaign as $campaignDetail) {
                        if ($campaignDetail['status'] == 1) {
                            $this->updateFeeAttributeHelper->update($campaignDetail, false);
                        }
                    }
                }else{
                    $this->_collection->create()->disableCampaignsDetails($data['id'])->getData();
                    foreach ($getDetailsCampaign as $campaignDetail) {
                        $this->updateFeeAttributeHelper->delete($campaignDetail);
                    }
                }
            }
            $rowData->save();
            $this->messageManager->addSuccess(__('Item has been successfully saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('credomatic/campaign/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Intcomex_CredomaticMSI::credomatic_campana');
    }
}
