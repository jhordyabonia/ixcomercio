<?php

namespace Intcomex\CredomaticMasterCard\Controller\Custom;

use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Store\Model\ScopeInterface;

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
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        InvoiceService $invoiceService,
        Transaction $transaction,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->resultRedirect = $context->getResultFactory();
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->transaction = $transaction;
        $this->invoiceService = $invoiceService;
        $this->orderManagement = $orderManagement;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {

            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
            $customError = (string) $this->_scopeConfig->getValue('payment/credomaticmastercard/CustomErrorMsg',ScopeInterface::SCOPE_STORE);
            $modo =  $this->_scopeConfig->getValue('payment/credomaticmastercard/modo',ScopeInterface::SCOPE_STORE);
            $showCustomError = false;
            if($customError != '') {
                $showCustomError = true;
            }

            $body = $this->getRequest()->getParams();
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_trans_resp.log');
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);
            $this->logger->info(print_r($body,true));
            $this->logger->info('modo');
            $this->logger->info($modo);
            
            $order = $objectManager->create('\Magento\Sales\Api\Data\OrderInterfaceFactory')->create()->loadByIncrementId($body['orderid']);
            
            if(empty($body)||isset($body['empty'])){
                $resultRedirect = $this->cancelOrder($this->logger,$body,true,$showCustomError,$customError,$order);
                return $resultRedirect;
            }
            
           
            if($modo=='pruebas'){
                $order->setState("processing")->setStatus("processing");
                $payment = $order->getPayment();
                $payment->setLastTransId(11222334455);
                $payment->save();
                $order->save();
                
                $this->_checkoutSession->setLastQuoteId($order->getId());
                $this->_checkoutSession->setLastSuccessQuoteId($order->getId());
                $this->_checkoutSession->setLastOrderId($order->getId()); // Not incrementId!!
                $this->_checkoutSession->setLastRealOrderId($body['orderid']);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/onepage/success');

            }else{
                if($body['response_code']==300||$body['response_code']==200){
    
                    $resultRedirect = $this->cancelOrder($this->logger,$body,false,$showCustomError,$customError,$order);
    
                }else if($body['response_code']==100){
                    $order->setState("processing")->setStatus("processing");
                    $payment = $order->getPayment();
                    $payment->setLastTransId($body['authcode']);
                    $payment->save();
                    $order->save();
                    $this->_checkoutSession->setLastQuoteId($order->getId());
                    $this->_checkoutSession->setLastSuccessQuoteId($order->getId());
                    $this->_checkoutSession->setLastOrderId($order->getId()); // Not incrementId!!
                    $this->_checkoutSession->setLastRealOrderId($body['orderid']);
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('checkout/onepage/success');
                }

            }
            
            $this->orderSender->send($order, true);

            if ($order->canInvoice()) {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->register();
                $invoice->save();
                $transactionSave = $this->transaction->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();
                $this->invoiceSender->send($invoice);
                //Send Invoice mail to customer
                $order->addStatusHistoryComment(__('Notified customer about invoice creation #%1.', $invoice->getId()))->setIsCustomerNotified(true)->save();
            }
    
            return $resultRedirect;
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }
        
    }

    public function cancelOrder($loger,$body,$vacio=false,$showCustomError,$customError,$order){
        try {
            if($vacio){
                $loger->info('No se recibio respuesta de credomatic');
            }

            if( $showCustomError ) {
                $msgError = $customError;
            }else {
                $msgError = ((isset($body['responsetext']))?$body['responsetext']:'');
            }
            $this->_messageManager->addError($msgError);
   
            if ($order->getId()) {
                $order->setState("pending")->setStatus("pending");
                sleep(2);
                $order->setState("canceled")->setStatus("canceled");
                $order->cancel();
                $this->orderManagement->cancel($order->getId());
                $order->addStatusHistoryComment('Se cancela la order con el sigueinte error: '.((isset($body['responsetext']))?$body['responsetext']:''));
                $order->save();
            }
            $loger->info('Estado final de la orden');
            $loger->info($order->getState());
                 

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');
            
            if ($order->getPayment()) {
                if ($order->getData('state') === 'canceled') {
                   $this->_checkoutSession->restoreQuote();
                }
            } 
            
            return $resultRedirect;
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }
    }

}