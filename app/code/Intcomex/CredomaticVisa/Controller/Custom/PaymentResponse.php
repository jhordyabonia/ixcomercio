<?php

namespace Intcomex\CredomaticVisa\Controller\Custom;

use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Intcomex\Credomatic\Model\CredomaticFactory;

class PaymentResponse extends \Magento\Framework\App\Action\Action
{

    protected $resultRedirect;
    protected $_messageManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json,
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
        $this->json = $json;
        $this->_scopeConfig = $scopeConfig;
        $this->resultRedirect = $context->getResultFactory();
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->orderSender = $orderSender;
        $this->orderManagement = $orderManagement;
        $this->_credomaticFactory = $credomaticFactory;
        $this->_orderInterfaceFactory = $orderInterfaceFactory;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomaticvisa_trans_resp.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->customError = (string) $this->_scopeConfig->getValue('payment/credomaticvisa/CustomErrorMsg',ScopeInterface::SCOPE_STORE);
        $this->modo =  $this->_scopeConfig->getValue('payment/credomaticvisa/modo',ScopeInterface::SCOPE_STORE);
        $this->reintentos =  $this->_scopeConfig->getValue('payment/credomaticvisa/reintentos',ScopeInterface::SCOPE_STORE);
        $this->timeout =  $this->_scopeConfig->getValue('payment/credomaticvisa/timeout',ScopeInterface::SCOPE_STORE);
        $this->username =  $this->_scopeConfig->getValue('payment/credomaticvisa/usuario',ScopeInterface::SCOPE_STORE);
        $this->password =  $this->_scopeConfig->getValue('payment/credomaticvisa/password',ScopeInterface::SCOPE_STORE);
        $this->urlQueryApi =  $this->_scopeConfig->getValue('payment/credomaticvisa/url_api',ScopeInterface::SCOPE_STORE);
        $this->_curl = $curl;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {
            $get = $this->getRequest()->getParams();

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');

            if(!empty($get)){
                $model =  $this->_credomaticFactory->create();  
                if(isset($get['token'])&&!empty($get['token'])){

                    $data = $model->load($get['token'],'token');
                
                    if(!empty($data->getData())){ 
                        $model->setResponse($this->json->serialize($get));
                        $model->setUpdatedAt();
                        $model->save();

                        if($this->checkAndProcess($this->json->serialize($get))){
                            $resultRedirect->setPath('checkout/onepage/success');
                        }
                    }

                }
                
            }
            if($this->cancelOrderNoParams()){
                return $resultRedirect;
            }
            return $resultRedirect;

        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage())); 
        }
        
    }

    public function cancelOrder($body,$order){
        try {
            $response = json_decode($body,true);
            $order->addStatusToHistory($order->getStatus(), 'Se procede a cancelar la orden');
            $this->_messageManager->addError($this->customError);

            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $payment = $order->getPayment();
            if(isset($response['authcode'])){
                $payment->setLastTransId($response['authcode']);
            }
            if(!empty($response)){
                $payment->setAdditionalInformation('payment_resp',json_encode($response));
            }
            $order->setIsPaidCredo('No');
            $order->save();    
                 
            $this->_checkoutSession->restoreQuote();
            return false;
        } catch (\Exception $e) {
            $this->logger->info("cancelOrder_exception: " . $e->getMessage());
            return false;
        }
    }

    public function cancelOrderNoParams(){
        try {
            $order = $this->_orderInterfaceFactory->create()->load($this->_checkoutSession->getLastOrderId());
            $order->addStatusToHistory($order->getStatus(), 'Se procede a cancelar la orden por falta de parametros');
            $this->_messageManager->addError($this->customError);

            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->setIsPaidCredo('No');
            $order->save();    
            $this->_checkoutSession->restoreQuote();
            $this->logger->info("cancelOrderNoParams: Se procede a cancelar la orden por falta de parametros");
            return true;
        } catch (\Exception $e) {
            $this->logger->info("cancelOrderNoParams_exception: " . $e->getMessage());
            return false;

        }
    }

    public function checkAndProcess($body){

        $order = $this->_orderInterfaceFactory->create()->load($this->_checkoutSession->getLastOrderId());

        try {
            $this->logger->info("checkAndProcess_response: " . $body);
            
            if(!$body){
                return false;
            }
            
            $response = json_decode($body,true);
            
            if(!strcmp($response['response_code'], '100')){
                return $this->processOrder($body);
            }else{ 
                return $this->cancelOrder($body,$order);
            }
            
        } catch (\Exception $e) {
            $this->logger->info("checkAndProcess_exception: " . $e->getMessage());
            return false;
        }
    }

    public function processOrder($body){

        try {
            $response = json_decode($body,true);
            $order = $this->_orderInterfaceFactory->create()->load($this->_checkoutSession->getLastOrderId());
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->addStatusToHistory($order->getStatus(), 'Order processing  successfully');
            $payment = $order->getPayment();
            $payment->setLastTransId($response['transactionid']);
            $payment->setAdditionalInformation('payment_resp',json_encode($response));
            $order->setIsPaidCredo('Yes');
            $order->addStatusToHistory($order->getStatus(), 'Order update: last trans id and additional information');
            $order->save();                        
            $this->orderSender->send($order, true);
            $order->addStatusToHistory($order->getStatus(), 'Order Send Email');
            $this->logger->info("processOrder: " . $body);
            return true;
        } catch (\Exception $e) {
            $this->logger->info("processOrder_Exception : " . $e->getMessage());
            return false;
        }
    }
}