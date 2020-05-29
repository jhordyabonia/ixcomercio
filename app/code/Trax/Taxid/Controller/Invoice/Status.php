<?php
/** 
 * @category    Mienvio
 * @package     Mienvio_Api
 * @author      Valentina Aguirre
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
 */

namespace Trax\Taxid\Controller\Invoice;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

/**
 * Webhook class  
 */
class Status extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface 
{
    
    private $helper;
	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $resultPageFactory;
    protected $request;
    protected $payment;
    protected $checkoutSession;
    protected $orderRepository;
    protected $logger;
    protected $_invoiceService;
    protected $transactionBuilder;
    protected $_iwsOrder;
    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $jsonResultFactory;
    protected $_orderCollectionFactory;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * @var OrderViewAuthorizationInterface
     */
    protected $orderAuthorization;
    
    /**
     * 
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger_interface
     * @param \Magento\Framework\Controller\ResultFactory $result
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Trax\Catalogo\Helper\Email $email
     * @param \Trax\Ordenes\Model\IwsOrderFactory $iwsOrder,
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderAuthorization
     */
    public function __construct(
            Context $context, 
            PageFactory $resultPageFactory, 
            \Magento\Framework\App\Request\Http $request, 
            \Magento\Checkout\Model\Session $checkoutSession,
            \Psr\Log\LoggerInterface $logger_interface,
            \Magento\Framework\Controller\ResultFactory $result,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Trax\Catalogo\Helper\Email $email,
            \Trax\Ordenes\Model\IwsOrderFactory $iwsOrder,
            \Magento\Sales\Model\OrderFactory $orderFactory,
            \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderAuthorization 
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger_interface;        
        $this->resultRedirect = $result;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $email;
        $this->_iwsOrder = $iwsOrder;
        $this->orderFactory = $orderFactory;
        $this->orderAuthorization = $orderAuthorization;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Load the page defined in view/frontend/layout/bancomer_index_webhook.xml
     * URL /openpay/payment/success
     * 
     * @url https://magento.stackexchange.com/questions/197310/magento-2-redirect-to-final-checkout-page-checkout-success-failed?rq=1
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {    
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        if($customerSession->isLoggedIn()) {
            if($this->getRequest()->getParam('order_id') != null){
                $order_id = (int)$this->getRequest()->getParam('order_id', false);
                $order = $this->orderFactory->create()->load($order_id);
                if ($this->orderAuthorization->canView($order)) {
                    $iws_order = $this->loadIwsData($order_id);
                    if($iws_order){
                        $resultPage = $this->resultPageFactory->create();
                        $resultPage->getConfig()->getTitle()->set((__('Order' . ' # '.$order->getRealOrderId())));
                        $resultPage->getLayout()->initMessages();          
                        try {               
                            $resultPage->getLayout()->getBlock('invoice_status')->setTitle("Order Status");     
                            $resultPage->getLayout()->getBlock('invoice_status')->setOrderStatus($order->getStatus());     
                            $resultPage->getLayout()->getBlock('invoice_status')->setInvoiceData(unserialize($iws_order->getTraxInvoice())); 
                        } catch (\Exception $e) {
                            $this->logger->error('#SUCCESS', array('message' => $e->getMessage(), 'code' => $e->getCode(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()));
                            $resultPage->getLayout()->getBlock('invoice_status')->setTitle("Error");
                        }        
                        return $resultPage;
                    } else {
                        $this->messageManager->addError( __("The order has not an invoice.") );
                        $this->_redirect('sales/order/history');                 
                    }
                } else {
                    $this->messageManager->addError( __('There was an error checking the order information.') );
                    $this->_redirect('sales/order/history');                 
                }
            } else {    
                $this->messageManager->addError( __('There was an error checking the order information.') );
                $this->_redirect('sales/order/history');
            }
        } else {
            $this->messageManager->addError( __('To check the invoice information of your order you must login.') );
            $this->_redirect('customer/account/');
        }
    }

    //Se guarda información de IWS en tabla custom
    public function loadIwsData($order_id) 
    {
        $orders = $this->_iwsOrder->create();
        $orders->getResource()
            ->load($orders, $order_id, 'order_id');
        if($orders->getId()){
            return $orders;
        }
        return false;
	}
}
