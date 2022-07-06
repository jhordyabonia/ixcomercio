<?php

namespace Intcomex\CredomaticMSI\Controller\Adminhtml\CampaignDetail;

use Intcomex\CredomaticMSI\Helper\UpdateFeeAttributeHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Intcomex\CredomaticMSI\Model\CampaignDetailFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Save extends Action
{
    /** @var CampaignDetailFactory */
    private $campaignDetailFactory;

    /** @var UpdateFeeAttributeHelper */
    private $helper;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param Context $context
     * @param CampaignDetailFactory $campaignDetailFactory
     * @param UpdateFeeAttributeHelper $helper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        CampaignDetailFactory $campaignDetailFactory,
        UpdateFeeAttributeHelper $helper,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->campaignDetailFactory = $campaignDetailFactory;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $edit = false;
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('credomatic/campaigndetail/addrow');
            return;
        }
        try {
            //Check if product exists
            $this->productRepository->get($data['sku']);

            $rowData = $this->campaignDetailFactory->create();
            $rowData->setData($data);
            $rowData['hash'] = $rowData['sku'] . $rowData['campaign_id'] . $rowData['fee'] ;
            if (isset($data['id'])) {
                $rowData->setEntityId($data['id']);
                $edit = true;
            }
            $rowData->save();
            if ($data['status'] == 1) {
                $this->helper->update($rowData,$edit);
            }else{
                $this->helper->delete($rowData);
            }
            $this->messageManager->addSuccess(__('Item has been successfully saved.'));
        } catch (\Exception $e) {
            if ($e->getMessage() == "Unique constraint violation found") {
                $this->messageManager->addError(__('Duplicate entry.'));
            }else{
                $this->messageManager->addError(__($e->getMessage()));
            }
        }
        $this->_redirect('credomatic/campaigndetail/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Intcomex_CredomaticMSI::credomatic_detalle_campana');
    }
}
