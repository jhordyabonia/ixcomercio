<?php

namespace Intcomex\CredomaticMSI\Controller\Adminhtml\Campaign;

use Magento\Framework\Controller\ResultFactory;

class AddRow extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Intcomex\CredomaticMSI\Model\CampaignFactory
     */
    private $campaignFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry,
     * @param \Intcomex\CredomaticMSI\Model\CampaignFactory $campaignFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Intcomex\CredomaticMSI\Model\CampaignFactory $campaignFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->campaignFactory = $campaignFactory;
    }

    /**
     * Mapped Grid List page.
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $rowId = (int) $this->getRequest()->getParam('id');
        $rowData = $this->campaignFactory->create();
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        if ($rowId) {
            $rowData = $rowData->load($rowId);
            $rowTitle = $rowData->getTitle();
            if (!$rowData->getId()) {
                $this->messageManager->addError(__('item no longer exist.'));
                $this->_redirect('credomatic/campaign/index');
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
        return $this->_authorization->isAllowed('Intcomex_CredomaticMSI::credomatic_campana');
    }
}
