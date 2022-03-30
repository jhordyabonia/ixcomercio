<?php

namespace Intcomex\Credomatic\Controller\Custom;

use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Intcomex\Credomatic\Model\CredomaticFactory;

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
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Intcomex\Credomatic\Model\CredomaticFactory $credomaticFactory,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->resultRedirect = $context->getResultFactory();
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->orderSender = $orderSender;
        $this->orderManagement = $orderManagement;
        $this->_credomaticFactory = $credomaticFactory;
        $this->_orderInterfaceFactory = $orderInterfaceFactory;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_trans_resp.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->customError = (string) $this->_scopeConfig->getValue('payment/credomatic/CustomErrorMsg',ScopeInterface::SCOPE_STORE);
        $this->modo =  $this->_scopeConfig->getValue('payment/credomatic/modo',ScopeInterface::SCOPE_STORE);
        $this->reintentos =  $this->_scopeConfig->getValue('payment/credomatic/reintentos',ScopeInterface::SCOPE_STORE);
        $this->timeout =  $this->_scopeConfig->getValue('payment/credomatic/timeout',ScopeInterface::SCOPE_STORE);
        $this->username =  $this->_scopeConfig->getValue('payment/credomatic/usuario',ScopeInterface::SCOPE_STORE);
        $this->password =  $this->_scopeConfig->getValue('payment/credomatic/password',ScopeInterface::SCOPE_STORE);
        $this->urlQueryApi =  $this->_scopeConfig->getValue('payment/credomatic/url_api',ScopeInterface::SCOPE_STORE);
        $this->_curl = $curl;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {

            $this->processData(0);    
        
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage())); 
        }
        
    }

    public function cancelOrder($body,$orderId){
        try {

            $order = $this->_orderInterfaceFactory->create()->loadByIncrementId($orderId);

            $this->_messageManager->addError($this->customError);

            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $payment = $order->getPayment();
            if(isset($body['authcode'])){
                $payment->setLastTransId($body['authcode']);
            }
            if(!empty($body)){
                $payment->setAdditionalInformation('payment_resp',json_encode($body));
            }
            $order->setIsPaidCredo('No');
            $order->save();    
                 
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->_checkoutSession->restoreQuote();
            $resultRedirect->setPath('checkout/cart');
            return $resultRedirect;

        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage())); 
        }
    }

    public function checkAndProcess($body,$orderId){
        try {
            $order = $this->_orderInterfaceFactory->create()->loadByIncrementId($orderId);
            if($this->modo=='pruebas'){
                    $this->processOrder($body,'1234567890',$order);
            }else{
                if($body['response_code']!=100){

                    $this->cancelOrder($body,$order);
                }else{

                    $this->processOrder($body,$body['authcode'],$order);
                }
            }
            $this->orderSender->send($order, true);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage())); 
        }
    }

    public function processOrder($body,$transactionId,$order){

        try {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->addStatusToHistory($order->getStatus(), 'Order processing  successfully');
            $payment = $order->getPayment();
            $payment->setLastTransId($transactionId);
            $payment->setAdditionalInformation('payment_resp',json_encode($body));
            $order->setIsPaidCredo('Yes');
            $order->save();
            $this->_checkoutSession->setLastQuoteId($order->getId());
            $this->_checkoutSession->setLastSuccessQuoteId($order->getId());
            $this->_checkoutSession->setLastOrderId($order->getId()); // Not incrementId!!
            $this->_checkoutSession->setLastRealOrderId($body['orderid']);
            $resultRedirect->setPath('checkout/onepage/success');
            return $resultRedirect;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage())); 
        }
    }

    public function processData($attempts){
        //$orderId = $this->_checkoutSession->getLastRealOrderId();
        $orderId = '11000000708';
        $respAndVerify = $this->respAndVerify($orderId);

        print_r($this->reintentos);

      if($attempts>$this->reintentos){
            $this->logger->info('Se cumplen la cantidad de reintentos para '.$orderId.' - '.$attempts);
            $this->cancelOrder($respAndVerify,$orderId);
       }else{
           if(!$respAndVerify){
                $attempts++;
                sleep($this->timeout);
                $this->processData($attempts);
           }else{
                $this->checkAndProcess($respAndVerify,$orderId);
           }
       }
  
    }


    public function respAndVerify($orderId){
        $model =  $this->_credomaticFactory->create();  
        $data = $model->getCollection()->addFieldToFilter('order_id', array('eq' => $orderId));
        if(empty($data->getData())){
            return false;
        }
        $dataArray = $data->getData();
        $this->logger->info(print_r($dataArray,true));

        //validate transaction
        $params = array(
            'username' => $this->username,
            'password' => $this->password,
            'order_id' => $orderId
        );


        $this->_curl->post($this->urlQueryApi, $params); 

        $dataResp =  $this->_curl->getBody();
        $this->logger->info('Respuesta servicio Credomatic');

        $xml=simplexml_load_string($dataResp);
        
        if(empty($xml)||!isset($xml->transaction)){
            return false;
        }
        return $dataArray[0];
    }

}