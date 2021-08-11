<?php
namespace Intcomex\CredomaticMasterCard\Controller\Custom;

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
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->encryptor = $encryptor;
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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_request.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
       

            $post  = $this->getRequest()->getPostValue();
            
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
            $orderId =  $this->_checkoutSession->getLastOrderId();
            $order = $objectManager->get('\Magento\Sales\Model\Order')->load($orderId)->getData();
            
            $arrayData['key_id'] = $this->_scopeConfig->getValue('payment/credomaticmastercard/key_id',ScopeInterface::SCOPE_STORE);
            $arrayData['processor_id'] = $this->_scopeConfig->getValue('payment/credomaticmastercard/processor_id'.$post['cuotas'],ScopeInterface::SCOPE_STORE);
            $arrayData['amount'] = number_format($order['grand_total'],2,".","");
            $arrayData['orderid'] = $order['increment_id']; 

            $this->logger->info('Data send to credomatic');
            $this->logger->info(print_r($arrayData,true));
            $this->logger->info('- - - - ');

            $arrayData['data1'] = $this->encrypt($post['cvv_']); 
            $arrayData['data2'] = $this->encrypt($post['number']); 
            $arrayData['data3'] = $this->encrypt(str_pad($post['month'], 2, '0', STR_PAD_LEFT).substr($post['year'], 2, 4));
            $dataToPost = array();
            $dataToPost['info'] = http_build_query($arrayData);
            $dataToPost['orderid'] = $order['increment_id'];

        } catch (\Exception $e) {
             
            $dataToPost = ['error' => 'true', 'message' => $e->getMessage()];
        }

        $resultJson->setData($dataToPost);
        return $resultJson;

    }

    public function encrypt($data){
        $encrypt =  $this->encryptor->encrypt($data);
        return $encrypt;
    }
    

}