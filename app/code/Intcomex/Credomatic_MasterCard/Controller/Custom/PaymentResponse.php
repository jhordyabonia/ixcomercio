<?php

namespace Intcomex\Credomatic_MasterCard\Controller\Custom;

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
        Transaction $transaction
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
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {

            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
            $customError = (string) $this->_scopeConfig->getValue('payment/credomatic_masterCard/CustomErrorMsg');
            $modo =  $this->_scopeConfig->getValue('payment/credomatic_masterCard/modo',ScopeInterface::SCOPE_STORE);
            $showCustomError = false;
            if($customError != '') {
                $showCustomError = true;
            }

            $body = $this->getRequest()->getParams();
            sleep(1);
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_mastercard_trans_resp.log');
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);
            $this->logger->info(print_r($body,true));
            $this->logger->info('modo');
            $this->logger->info($modo);
            if(empty($body)){
                $resultRedirect = $this->cancelOrder($this->logger,$body,true,$showCustomError,$customError);
                return $resultRedirect;
            }
            if($modo=='pruebas'){
                
                $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($body['orderid']);
                $order->setState("processing")->setStatus("processing");
                $payment = $order->getPayment();
                $payment->setLastTransId(11222334455);
                $payment->save();
                $order->save();
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/onepage/success');

            }else{
                if($body['response_code']==300||$body['response_code']==200){
    
                    $resultRedirect = $this->cancelOrder($this->logger,$body,false,$showCustomError,$customError);
    
                }else if($body['response_code']==100){
                    $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($body['orderid']);
                    $order->setState("processing")->setStatus("processing");
                    $payment = $order->getPayment();
                    $payment->setLastTransId($body['authcode']);
                    $payment->save();
                    $order->save();
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('checkout/onepage/success');
                }

            }
            
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($body['orderid']);
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
                $order->addStatusHistoryComment(
                    __('Notified customer about invoice creation #%1.', $invoice->getId())
                )
                    ->setIsCustomerNotified(true)
                    ->save();
            }
    
            return $resultRedirect;
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }
        
    }

    public function cancelOrder($loger,$body,$vacio=false,$showCustomError,$customError){
        if($vacio){
            $loger->info('No se recibio respuesta de credomatic');
        }

        if( $showCustomError ) {
            $msgError = $customError;
        }else {
            $msgError = $body['responsetext'];
        }
        $this->_checkoutSession->setErrorMessage($msgError);
        $this->_messageManager->addError($msgError);
        $lastRealOrder = $this->_checkoutSession->getLastRealOrder();
        //$lastRealOrder->setState("canceled")->setStatus("canceled");
        $lastRealOrder->addStatusHistoryComment('Se cancela la order con el sigueinte error: '.$msgError);
        $lastRealOrder->cancel();  
        $lastRealOrder->save();

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/cart');
        $loger->info($lastRealOrder->getData('status'));

        if ($lastRealOrder->getPayment()) {
            if ($lastRealOrder->getData('state') === 'canceled' && $lastRealOrder->getData('status') === 'canceled') {
                $this->_checkoutSession->restoreQuote();
            }
        } 
        return $resultRedirect;
    }

}