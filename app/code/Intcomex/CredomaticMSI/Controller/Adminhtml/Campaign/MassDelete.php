<?php

namespace Intcomex\CredomaticMSI\Controller\Adminhtml\Campaign;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Intcomex\CredomaticMSI\Helper\UpdateFeeAttributeHelper;
use Intcomex\CredomaticMSI\Model\ResourceModel\Campaign\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter.
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /** 
    * @var UpdateFeeAttributeHelper 
    */
    private $helper;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     * @param UpdateFeeAttributeHelper $helper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        UpdateFeeAttributeHelper $helper
    ) {
        $this->helper = $helper;
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $recordDeleted = 0;
        foreach ($collection->getItems() as $record) {
            $getDetailsCampaign = $this->_collectionFactory->create()->getDetailsCampaign($record->getId())->getData();
            foreach ($getDetailsCampaign as $campaignDetail) {
                $this->helper->delete($campaignDetail);
            }
            $record->setId($record->getId());
            $record->delete();
            
            $recordDeleted++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $recordDeleted));

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * Check Category Map recode delete Permission.
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Intcomex_CredomaticMSI::credomatic_campana');
    }
}
