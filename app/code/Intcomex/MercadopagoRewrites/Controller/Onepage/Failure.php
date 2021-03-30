<?php

namespace Intcomex\MercadopagoRewrites\Controller\Onepage;

class Failure extends \Magento\Checkout\Controller\Onepage
{
    /**
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_checkoutSession = $objectManager->create('\Magento\Checkout\Model\Session');
        $_quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
        
        $order = $_checkoutSession->getLastRealOrder();
        $quote = $_quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
        
        if ($quote->getId()) {
            $quote->setIsActive(1)->setReservedOrderId(null)->save();
            $_checkoutSession->replaceQuote($quote);
        }
        return $this->resultPageFactory->create();
    }
}
