<?php

namespace Intcomex\CredomaticVisa\Controller\Custom;
use Magento\Store\Model\ScopeInterface;

class GetOrder extends \Magento\Framework\App\Action\Action
{

    /**
    * @var \Magento\Framework\Controller\Result\JsonFactory
    */
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Order $modelOrder,
        \Magento\Store\Model\StoreManagerInterface  $storeManagerInterface,
        \Intcomex\Credomatic\Model\CredomaticFactory $credomaticFactory
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->encryptor = $encryptor;
        $this->_modelOrder = $modelOrder;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->_credomaticFactory = $credomaticFactory;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        
        try {
        $resultJson = $this->resultJsonFactory->create();
            $arrayData = array();
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomaticvisa_request.log');
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);

            $post  = $this->getRequest()->getPostValue();
            $orderId =  $this->_checkoutSession->getLastOrderId();
            $order = $this->_modelOrder->load($orderId);

            $arrayData['key'] = $this->_scopeConfig->getValue('payment/credomaticvisa/key',ScopeInterface::SCOPE_STORE);
            $arrayData['key_id'] = $this->_scopeConfig->getValue('payment/credomaticvisa/key_id',ScopeInterface::SCOPE_STORE);
            $arrayData['processor_id'] = $this->_scopeConfig->getValue('payment/credomaticvisa/processor_id'.$post['cuotas'],ScopeInterface::SCOPE_STORE);
            $arrayData['amount'] = number_format($order->getGrandTotal(),2,".","");
            $arrayData['orderid'] = $order->getIncrementId();
            $token = substr(md5(uniqid(rand())), 0, 49);

            $arrayData['type'] = 'sale';
            $arrayData['key'] = $this->_scopeConfig->getValue('payment/credomaticvisa/key',ScopeInterface::SCOPE_STORE);
            $arrayData['key_id'] = $this->_scopeConfig->getValue('payment/credomaticvisa/key_id',ScopeInterface::SCOPE_STORE);
            $arrayData['amount'] = number_format($order->getGrandTotal(),2,".","");
            $arrayData['time'] = strtotime(date('Y-m-d H:i:s'));
            $arrayData['hash'] = md5($order->getIncrementId().'|'.$arrayData['amount'].'|'.$arrayData['time'].'|'.$this->_scopeConfig->getValue('payment/credomatic/key',ScopeInterface::SCOPE_STORE));
            $arrayData['orderid'] = $order->getIncrementId();
            $arrayData['processor_id'] = $this->_scopeConfig->getValue('payment/credomaticvisa/processor_id'.$post['cuotas'],ScopeInterface::SCOPE_STORE);
            $arrayData['firstname'] = $billingAddress->getFirstname();
            $arrayData['lastname'] = $billingAddress->getLastname();
            $arrayData['email'] = $billingAddress->getEmail();
            $arrayData['phone'] = $billingAddress->getTelephone();
            $arrayData['street1'] = isset($billingAddress->getStreet()[0]) ? $billingAddress->getStreet()[0] : '';
            $arrayData['street2'] = isset($billingAddress->getStreet()[1]) ? $billingAddress->getStreet()[1] : '';
            $arrayData['ccexp'] = str_pad($post['month'], 2, '0', STR_PAD_LEFT).substr($post['year'], 2, 4);
            $arrayData['redirect'] = $this->storeManagerInterface->getStore()->getBaseUrl().'credomaticvisa/custom/paymentresponse?token='.$token.'';
            $arrayData['url_gateway'] = $this->_scopeConfig->getValue('payment/credomaticvisa/url_gateway',ScopeInterface::SCOPE_STORE);
            $url_gateway = $this->_scopeConfig->getValue('payment/credomaticvisa/url_gateway',ScopeInterface::SCOPE_STORE);

            
            $model =  $this->_credomaticFactory->create();
            $model->addData([
                'order_id' => $order->getIncrementId(),
                'token' => $token,
                'created_at' => $arrayData['time'],
            ]);
            $model->save();

            $this->logger->info('Data send to credomatic');
            $this->logger->info(print_r($arrayData,true));
            $this->logger->info('- - - - ');
            
            //$arrayData['data1'] = $this->encrypt($post['cvv_']); 
            //$arrayData['data2'] = $this->encrypt($post['number']); 
            $arrayData['data3'] = str_pad($post['month'], 2, '0', STR_PAD_LEFT).substr($post['year'], 2, 4);
            $arrayData['orderid'] = $order->getIncrementId();

        } catch (\Exception $e) {
             
            $arrayData = ['error' => 'true', 'message' => $e->getMessage()];
        }

        $resultJson->setData($arrayData);
        return $resultJson;
        

    }

    public function encrypt($data){
        $encrypt =  $this->encryptor->encrypt($data);
        return $encrypt;
    }
    

}