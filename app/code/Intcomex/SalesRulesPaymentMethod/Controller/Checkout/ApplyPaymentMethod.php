<?php

namespace Intcomex\SalesRulesPaymentMethod\Controller\Checkout;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Quote\Model\Quote;

class ApplyPaymentMethod extends Action
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        LayoutFactory $layoutFactory,
        Cart $cart
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->layoutFactory = $layoutFactory;
        $this->cart = $cart;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $pMethod = $this->getRequest()->getParam('payment_method');

        /** @var Quote $quote */
        $quote = $this->cart->getQuote();

        $quote->getPayment()->setMethod($pMethod['method']);

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();

        $quote->save();
    }
}