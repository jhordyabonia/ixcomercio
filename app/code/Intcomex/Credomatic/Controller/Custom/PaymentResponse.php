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
        \Magento\Framework\Message\ManagerInterface $messageManager 
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->resultRedirect = $context->getResultFactory();
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        ini_set('display_errors', 1);
        try {

            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
            $customError = (string) $this->_scopeConfig->getValue('payment/Credomatic/CustomErrorMsg');
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
                if( $showCustomError ) {
                    $msgError = $showCustomError;
                }else {
                    $msgError = $body['responsetext'];
                }
                $this->_checkoutSession->setErrorMessage($msgError);
                $this->_messageManager->addError($msgError);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout');

            }else if($body['response_code']==100){
                $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($body['orderid']);
                $payment = $order->getPayment();
                $payment->setLastTransId($body['transactionid']);
                $this->logger->info('Transactionid');
                $this->logger->info($payment->getData());
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/onepage/success');
            }
            return $resultRedirect;
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }
        
    }

}