<?php

namespace Intcomex\Credomatic\Controller\Custom;

use Magento\Framework\Controller\ResultFactory;

class PaymentResponse extends \Magento\Framework\App\Action\Action
{

    protected $resultRedirect;
    protected $_messageManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\ResultFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->resultRedirect = $context->getResultFactory();
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {

            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
            $customError = (string) $this->_scopeConfig->getValue('payment/credomatic/CustomErrorMsg');
            $showCustomError = false;
            if($customError != '') {
                $showCustomError = true;
            }

            $body = $_GET;

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_trans_resp.log');
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);
            $this->logger->info(print_r($body,true));
            
            if($body['response_code']==300||$body['response_code']==200){

                $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($body['orderid']);
                $order->setState("canceled")->setStatus("canceled");
                $order->save();

                if( $showCustomError ) {
                    $msgError = $customError;
                }else {
                    $msgError = $body['responsetext'];
                }
                $this->_checkoutSession->setErrorMessage($msgError);
                $this->_messageManager->addError($msgError);
                $lastRealOrder = $this->_checkoutSession->getLastRealOrder();

                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/cart');
                $this->logger->info($lastRealOrder->getData('status'));

                if ($lastRealOrder->getPayment()) {
                    if ($lastRealOrder->getData('state') === 'canceled' && $lastRealOrder->getData('status') === 'canceled') {
                        $this->_checkoutSession->restoreQuote();
                    }
                }

            }else if($body['response_code']==100){
                $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($body['orderid']);
                $payment = $order->getPayment();
                $payment->setLastTransId($body['authcode']);
                $payment->save();
                $this->logger->info('Transactionid');
                $this->logger->info($payment->getData());
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/onepage/success');
            }

        
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($body['orderid']);
            $this->orderSender->send($order, true);
    
            return $resultRedirect;
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }
        
    }

}