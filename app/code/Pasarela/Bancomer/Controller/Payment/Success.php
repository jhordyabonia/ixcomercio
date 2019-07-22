<?php
/** 
 * @category    Payments
 * @package     Pasarela_Bancomer
 * @author      Valentina Aguirre
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
 */

namespace Pasarela\Bancomer\Controller\Payment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Pasarela\Bancomer\Model\Payment as BancomerPayment;

/**
 * Webhook class  
 */
class Success extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $request;
    protected $payment;
    protected $checkoutSession;
    protected $orderRepository;
    protected $logger;
    protected $_invoiceService;
    protected $transactionBuilder;
    
    /**
     * 
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param BancomerPayment $payment
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger_interface
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     */
    public function __construct(
            Context $context, 
            PageFactory $resultPageFactory, 
            \Magento\Framework\App\Request\Http $request, 
            BancomerPayment $payment,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Psr\Log\LoggerInterface $logger_interface,
            \Magento\Sales\Model\Service\InvoiceService $invoiceService,
            \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->payment = $payment;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger_interface;        
        $this->_invoiceService = $invoiceService;
        $this->transactionBuilder = $transactionBuilder;
    }

    /**
     * Load the page defined in view/frontend/layout/bancomer_index_webhook.xml
     * URL /openpay/payment/success
     * 
     * @url https://magento.stackexchange.com/questions/197310/magento-2-redirect-to-final-checkout-page-checkout-success-failed?rq=1
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {                
        try {                        
            $order_id = $this->checkoutSession->getLastOrderId();
            $quote_id = $this->checkoutSession->getLastQuoteId();
            
            $this->checkoutSession->setLastSuccessQuoteId($quote_id);
            
            $this->logger->debug('getLastQuoteId: '.$quote_id);
            $this->logger->debug('getLastOrderId: '.$order_id);
            $this->logger->debug('getLastSuccessQuoteId: '.$this->checkoutSession->getLastSuccessQuoteId());
            $this->logger->debug('getLastRealOrderId: '.$this->checkoutSession->getLastRealOrderId());        
            
            $openpay = $this->payment->getBancomerInstance();                          
            $order = $this->orderRepository->get($order_id);        
            $customer_id = $order->getExtCustomerId();

            if ($customer_id) {
                $customer = $this->payment->getBancomerCustomer($customer_id);
                $charge = $customer->charges->get($this->request->getParam('id'));
            } else {
                $charge = $openpay->charges->get($this->request->getParam('id'));
            }

            $this->logger->debug('#SUCCESS', array('id' => $this->request->getParam('id'), 'status' => $charge->status));

            if ($order && $charge->status != 'completed') {
                $order->cancel();
                $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_CANCELED, __('Canceled by customer.'));
                $order->save();

                $this->logger->debug('#SUCCESS', array('redirect' => 'checkout/onepage/failure'));
                
                return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure');            
            }

            $status = \Magento\Sales\Model\Order::STATE_PROCESSING;
            $order->setState($status)->setStatus($status);
            $order->setTotalPaid($charge->amount);  
            $order->addStatusHistoryComment("Pago recibido exitosamente")->setIsCustomerNotified(true);            
            $order->save();        

            $invoice = $this->_invoiceService->prepareInvoice($order);        
            $invoice->setTransactionId($charge->id);
//            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
//            $invoice->register();            
            $invoice->pay()->save();

            $payment = $order->getPayment();                                
            $payment->setAmountPaid($charge->amount);
            $payment->setIsTransactionPending(false);
            $payment->save();
            
            $this->logger->debug('#SUCCESS', array('redirect' => 'checkout/onepage/success'));
            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
            
        } catch (\Exception $e) {
            $this->logger->error('#SUCCESS', array('message' => $e->getMessage(), 'code' => $e->getCode(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()));
            //throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }
        
        return $this->resultRedirectFactory->create()->setPath('checkout/cart'); 
    }
}