<?php

namespace Intcomex\CredomaticMasterCard\Controller\Custom;

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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomaticmastercard_trans_resp.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->customError = (string) $this->_scopeConfig->getValue('payment/credomaticmastercard/CustomErrorMsg',ScopeInterface::SCOPE_STORE);
        $this->modo =  $this->_scopeConfig->getValue('payment/credomaticmastercard/modo',ScopeInterface::SCOPE_STORE);
        $this->reintentos =  $this->_scopeConfig->getValue('payment/credomaticmastercard/reintentos',ScopeInterface::SCOPE_STORE);
        $this->timeout =  $this->_scopeConfig->getValue('payment/credomaticmastercard/timeout',ScopeInterface::SCOPE_STORE);
        $this->username =  $this->_scopeConfig->getValue('payment/credomaticmastercard/usuario',ScopeInterface::SCOPE_STORE);
        $this->password =  $this->_scopeConfig->getValue('payment/credomaticmastercard/password',ScopeInterface::SCOPE_STORE);
        $this->urlQueryApi =  $this->_scopeConfig->getValue('payment/credomaticmastercard/url_api',ScopeInterface::SCOPE_STORE);
        $this->_curl = $curl;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {
            $orderId = $this->_checkoutSession->getLastOrderId();
            $this->logger->info('Se inicia en modo '.$this->modo.' para la orden'.$orderId);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');
            // Se envia intento 0 a process data
            if($this->processData(0)){
                $resultRedirect->setPath('checkout/onepage/success');
            }
            return $resultRedirect;

        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage())); 
        }
        
    }

    public function cancelOrder($body,$order){
        try {
            $order->addStatusToHistory($order->getStatus(), 'Se procede a cancelar la orden');
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
                 
            $this->_checkoutSession->restoreQuote();
           return false;
        } catch (\Exception $e) {
           return false;
        }
    }

    public function checkAndProcess($body,$order){

        try {

            $response = json_decode($body['response'],true);
            
            if($this->modo=='pruebas'){
                   return $this->processOrder($body,$response['authcode'],$order);
            }else{
                if($response['response_code']!=100){
                    return $this->cancelOrder($body,$order);
                }else{ 
                    return $this->processOrder($body,$response['authcode'],$order);
                }
            }
            
        } catch (\Exception $e) {
            return false;
        }
    }

    public function processOrder($body,$transactionId,$order){

        try {
            $response = json_decode($body['response'],true);
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->addStatusToHistory($order->getStatus(), 'Order processing  successfully');
            $payment = $order->getPayment();
            $payment->setLastTransId($transactionId);
            $payment->setAdditionalInformation('payment_resp',json_encode($response));
            $order->setIsPaidCredo('Yes');
            $order->save();
            $this->_checkoutSession->setLastQuoteId($order->getId());
            $this->_checkoutSession->setLastSuccessQuoteId($order->getId());
            $this->_checkoutSession->setLastOrderId($order->getId()); // Not incrementId!!
            $this->_checkoutSession->setLastRealOrderId($body['order_id']);
            $this->orderSender->send($order, true);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function processData($attempts){

        $orderId = $this->_checkoutSession->getLastOrderId();
        $order = $this->_orderInterfaceFactory->create()->load($orderId);
        $respAndVerify = $this->respAndVerify($order->getIncrementId());
        
          if($attempts>$this->reintentos){
                $this->logger->info('Se cumplen la cantidad de reintentos para la orden '.$order->getIncrementId().' Se procede a cancelar');
                // Cancel order Siempre retorna false para devolver al usuario al carrito
                return $this->cancelOrder($respAndVerify,$order);
           }else{
               if(!$respAndVerify){
                $this->logger->info('Reintento No. '.$attempts .' para verificar la transaccion para la orden: '.$order->getIncrementId());
                    $attempts++;
                    sleep($this->timeout);
                   return $this->processData($attempts);
               }else{
                   return $this->checkAndProcess($respAndVerify,$order);
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
        $this->logger->info(print_r($xml,true));
        if(empty($xml)||!isset($xml->transaction)){
            $this->logger->info('No se encuentra el nodo xml->transaction en la respues o no existe en credomatic');
            return false;
        }

        return $dataArray[0];
    }

}