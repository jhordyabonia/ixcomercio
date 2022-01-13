<?php

namespace Magento\Pagalo\Controller\Custom;

use Intcomex\Credomatic\Model\CredomaticFactory;

class PlaceOrder extends \Magento\Framework\App\Action\Action
{


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Pagalo\Model\Payment $payment,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->payment = $payment;
        $this->resultJsonFactory = $resultJsonFactory;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {
            $resultJson = $this->resultJsonFactory->create();
            $post  = $this->getRequest()->getPostValue();
            $respProcess = array();

            $resp = $this->payment->capture($post);

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');

            return $resultRedirect;
    
        } catch (\Exception $e) {
            $respProcess = ['status' => 'error', 'message' => $e->getMessage()];
        }
        $resultJson->setData($respProcess);
        return $resultJson;
        
    }


}