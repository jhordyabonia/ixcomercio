<?php

namespace Intcomex\Bines\Controller\Bines;

use Intcomex\Bines\Model\Rule\Condition\BinCode;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class SetBinCode extends Action
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        JsonFactory $resultJsonFactory
    ) {
        $this->checkoutSession = $checkoutSession;
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
        $this->checkoutSession->getQuote()->getShippingAddress()->setData(BinCode::BIN_CODE, $this->getRequest()->getParam(BinCode::BIN_CODE));
        $this->checkoutSession->getQuote()->setTotalsCollectedFlag(false);
        $this->checkoutSession->getQuote()->collectTotals();
        $this->checkoutSession->getQuote()->save();
        return $this->resultJsonFactory->create()->setData(true);
    }
}
