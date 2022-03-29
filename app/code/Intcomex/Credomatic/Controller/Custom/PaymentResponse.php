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
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory
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
        $this->urlQueryApi =  $this->_scopeConfig->getValue('payment/credomatic/timeout',ScopeInterface::SCOPE_STORE);
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {

            $resultRedirect = $this->resultRedirectFactory->create();
            

            $showCustomError = false;
            if($this->customError != '') {
                $showCustomError = true;
            }
            $post  = $this->getRequest()->getPostValue();
            $body = json_decode($post['resp_info'],true);

            if(empty($body)||!isset($body['orderid']){
                $resultRedirect = $this->cancelOrder($body);
                $this->logger->info('parametro resp_info esta vacio');
                return $resultRedirect;
            }
            
            $this->processData($body,0);    
           
        
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }
        
    }

    public function cancelOrder($body){
        try {

            $orderId = $this->checkoutSession->getData(‘last_order_id’);
            $order = $this->_orderInterfaceFactory->create()->loadById($orderId['orderid']);

            $this->_messageManager->addError($this->customError);

            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $payment = $order->getPayment();
            if(isset($body['authcode'])){
                $payment->setLastTransId($transactionId);
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

    public function checkAndProcess($body){
        try {
            $order = $this->_orderInterfaceFactory->create()->loadByIncrementId($body['orderid']);
            if($this->modo=='pruebas'){
                    $this->processOrder($body,'1234567890',$order);
            }else{
                if($body['response_code']!=100){

                    $this->cancelOrder($body,$this->customError);
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

    public function processData($body,$attempts){

       if($attempts>$this->reintentos){
            $this->cancelOrder($body);
       }else{
           if(!$this->respAndVerify()){
                $attempts++;
                sleep($this->timeout);
                $this->processData($body,$attempts);
           }else{
                $this->checkAndProcess($body);
           }
       }
  
    }


    public function respAndVerify($body){

        $model =  $this->_credomaticFactory->create();  
        $data = $model->getCollection()
        ->addFieldToFilter('order_id', array('eq' => $body['orderid']))
        ->addFieldToFilter('token', array('eq' => $body['token']));
        if(empty($data->getData())){
            $this->logger->info('No se pudo verificar la identidad de la respuesta intento '.$atemps);
            return false;
        }

         return true;
    }

}