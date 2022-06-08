<?php

namespace Intcomex\CredomaticMSI\Controller\Adminhtml\CampaignDetail;

use Magento\Framework\Controller\ResultFactory;

class AddRow extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Intcomex\CredomaticMSI\Model\CampaignDetailFactory
     */
    private $campaignDetailFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry,
     * @param \Intcomex\CredomaticMSI\Model\CampaignDetailFactory $campaignDetailFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Intcomex\CredomaticMSI\Model\CampaignDetailFactory $campaignDetailFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->campaignDetailFactory = $campaignDetailFactory;
    }

    /**
     * Mapped Grid List page.
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $rowId = (int) $this->getRequest()->getParam('id');
        $rowData = $this->campaignDetailFactory->create();
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        if ($rowId) {
            $rowData = $rowData->load($rowId);
            $rowTitle = $rowData->getTitle();
            if (!$rowData->getId()) {
                $this->messageManager->addError(__('item no longer exist.'));
                $this->_redirect('credomatic/campaigndetail/index');
                return;
            }
        }

        $this->coreRegistry->register('row_data', $rowData);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title = $rowId ? __('Edit Item ').$rowTitle : __('Add Item');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Intcomex_CredomaticMSI::credomatic_detalle_campana');
    }
}
