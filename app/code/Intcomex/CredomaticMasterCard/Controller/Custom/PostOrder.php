<?php

namespace Intcomex\CredomaticMasterCard\Controller\Custom;

class PostOrder extends \Magento\Framework\App\Action\Action
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
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_status_after_postorder.log');
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);
            $post  = $this->getRequest()->getParams();
            if(!empty($post)){

                $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
                $storeManager =  $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

                $order = $objectManager->create('\Magento\Sales\Api\Data\OrderInterfaceFactory')->create()->loadByIncrementId($post['orderid']);
                $order->setState("pending")->setStatus("pending");
                $order->save();
                $this->logger->info('-----');
                $this->logger->info('status');
                $this->logger->info($post['orderid']);
                $this->logger->info($order->getState());
                $this->logger->info('-----');

                $time = strtotime(date('Y-m-d H:i:s'));
                $hash = md5($post['orderid'].'|'.$post['amount'].'|'.$time.'|'.$this->_scopeConfig->getValue('payment/credomaticmastercard/key'));
                $form = '<form action="https://credomatic.compassmerchantsolutions.com/api/transact.php" method="POST"   id="formCredomaticMasterCard">';
                $form .= '<input type="hidden" readonly id="credomatic_type" name="type" value="sale"  >';
                $form .= '<input type="hidden" readonly id="credomatic_key_id" name="key_id" value="'.$post['key_id'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_hash" name="hash" value="'.$hash.'" >';
                $form .= '<input type="hidden" readonly id="credomatic_time" name="time" value="'.$time.'" >';
                $form .= '<input type="hidden" readonly id="credomatic_amount" name="amount" value="'.$post['amount'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_orderid" name="orderid" value="'.$post['orderid'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_processor_id" name="processor_id" value="'.$post['processor_id'].'"  >';
                $form .= '<input type="hidden" readonly id="credomatic_cvv" name="cvv" value="'.$this->decrypt($post['data1']).'"  >';
                $form .= '<input type="hidden" readonly id="credomatic_ccnumber" name="ccnumber" value="'.$this->decrypt($post['data2']).'" >';
                $form .= '<input type="hidden" readonly id="credomatic_ccexp" name="ccexp" value="'.$this->decrypt($post['data3']).'"  >';
                $form .= '<input type="hidden" readonly id="credomatic_redirect" name="redirect" value="'.$storeManager->getStore()->getBaseUrl().'credomatic/custom/registerresponse"  >';
                $form .= '</form>';
                $form .= '<script>';
                $form .= 'setTimeout(function(){ document.getElementById("formCredomaticMasterCard").submit(); }, 2000)';
                $form .= '</script>';
                echo $form;
            }
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }

    }

    public function decrypt($value){
    return $this->encryptor->decrypt($value);
    }

}