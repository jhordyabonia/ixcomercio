<?php

namespace Cdi\Custom\Controller\Index;
use Magento\Store\Model\ScopeInterface;


class setPaymentInfo extends \Magento\Framework\App\Action\Action
{

    /**
    * @var \Magento\Framework\Controller\Result\JsonFactory
    */



    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        //die('test controller');
        $resultJson = $this->resultJsonFactory->create();

        $post  = $this->getRequest()->getPostValue();
        
        try {
            $order = $this->_checkoutSession->getQuote();
            $payment = $order->getPayment();
            $payment->setAdditionalInformation(array('useinvoice'=>$post['useinvoice']));
            $payment->save();
            $order->save();
            $arrayData['success'] = 'true';

        } catch (\Exception $e) {
             
            $arrayData = ['error' => 'true', 'message' => $e->getMessage()];
        }

        $resultJson->setData($arrayData);
        return $resultJson;


    }

}