<?php

namespace Intcomex\Bines\Controller\Bines;

use Intcomex\Bines\Api\Data\BinesInterface;
use Intcomex\Bines\Model\Bines\Attribute\Source\Status;
use Intcomex\Bines\Model\ResourceModel\Bines\CollectionFactory;
use Intcomex\Bines\Model\Rule\Condition\BinCampaign;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class SetBinCampaign extends Action
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param CollectionFactory $collectionFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CollectionFactory $collectionFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return Json
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(): Json
    {
        $ids = $this->collectionFactory->create()
            ->addFieldToFilter(BinesInterface::BIN_CODES, ['like' => '%' . $this->getRequest()->getParam('bin_code') . '%'])
            ->getAllIds();

        $this->checkoutSession->getQuote()->getShippingAddress()->setData(BinCampaign::CAMPAIGN, $ids);
        $this->checkoutSession->getQuote()->setTotalsCollectedFlag(false);
        $this->checkoutSession->getQuote()->collectTotals();
        $this->checkoutSession->getQuote()->save();
        return $this->resultJsonFactory->create()->setData($ids);
    }
}
