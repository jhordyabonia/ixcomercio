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

/**
 * Webhook class  
 */
class Error extends \Magento\Framework\App\Action\Action
{

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';

	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';

	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';

    const ORDENES_REINTENTOS = 'trax_general/ordenes_general/ordenes_reintentos';

    const ORDENES_CORREO = 'trax_general/ordenes_general/ordenes_correo';
    
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
    
    /**
     * 
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger_interface
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Pasarela\Bancomer\Model\BancomerTransaccionesFactory  $bancomerTransacciones
     * @param \Magento\Framework\Controller\ResultFactory $result
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Trax\Catalogo\Helper\Email $email
     */
    public function __construct(
            Context $context, 
            PageFactory $resultPageFactory, 
            \Magento\Framework\App\Request\Http $request, 
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Psr\Log\LoggerInterface $logger_interface,
            \Magento\Sales\Model\Service\InvoiceService $invoiceService,
            \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
            \Pasarela\Bancomer\Model\BancomerTransaccionesFactory  $bancomerTransacciones,
            \Magento\Framework\Controller\ResultFactory $result,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Trax\Catalogo\Helper\Email $email,
            \Trax\Ordenes\Model\IwsOrderFactory $iwsOrder
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger_interface;        
        $this->_invoiceService = $invoiceService;
        $this->transactionBuilder = $transactionBuilder;
        $this->_bancomerTransacciones = $bancomerTransacciones;
        $this->resultRedirect = $result;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $email;
        $this->_iwsOrder = $iwsOrder;
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
            /*$mp_order = $_POST['mp_order'];
            $mp_reference = $_POST['mp_reference'];
            $mp_amount = $_POST['mp_amount'];
            $mp_response = $_POST['mp_response'];
            $mp_authorization = $_POST['mp_authorization'];
            $mp_paymentMethod = $_POST['mp_paymentMethod'];
            $mp_cardType = $_POST['mp_cardType'];
            $mp_response = $_POST['mp_response'];
            $mp_date = $_POST['mp_date'];
            $mp_paymentMethodCode = $_POST['mp_paymentMethodCode'];
            $mp_signature = $_POST['mp_signature'];
            $mp_bankname = $_POST['mp_bankname'];
            $mp_bankcode = $_POST['mp_bankcode'];
            $mp_pan = $_POST['mp_pan'];
            $mp_saleid = $_POST['mp_saleid'];
            $mp_signature1 = hash('sha256', $mp_order.$mp_reference.$mp_amount.'.00'.$mp_authorization);*/
            $mp_order = "56";
            $mp_reference = "2000000086";
            $mp_amount = "133070,89";
            $mp_paymentMethod = "TDX";
            $mp_cardType = "credito";
            $mp_response = "0";
            $mp_responsemsg = "Transacción autorizada";
            $mp_authorization = "abc123";
            $mp_date = "2019-07-25 16:15:15";
            $mp_paymentMethodCode = "123";
            $mp_bankname = "Marvel Bank";
            $mp_bankcode = "BANXICO";
            $mp_saleid = "1";
            $mp_pan = "12345678";
            $mp_signature = hash('sha256', $mp_order.$mp_reference.$mp_amount.'.00'.$mp_authorization);
            $mp_signature1 = hash('sha256', $mp_order.$mp_reference.$mp_amount.'.00'.$mp_authorization);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->initMessages();
            //TODO: Cancelar orden
            $this->cancelOrder($mp_order);
            $resultPage->getLayout()->getBlock('bancomer_success')->setTitle("Transacción Cancelada");         
            $resultPage->getLayout()->getBlock('bancomer_success')->setOrder($mp_order);
            $resultPage->getLayout()->getBlock('bancomer_success')->setReference($mp_reference);
            $resultPage->getLayout()->getBlock('bancomer_success')->setAmount($mp_amount);
            $resultPage->getLayout()->getBlock('bancomer_success')->setPaymentMethod($mp_paymentMethod);
            $resultPage->getLayout()->getBlock('bancomer_success')->setResponse($mp_responsemsg);
        } catch (\Exception $e) {
            $this->logger->error('#SUCCESS', array('message' => $e->getMessage(), 'code' => $e->getCode(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()));
            $resultPage->getLayout()->getBlock('bancomer_success')->setTitle("Error");
        }
        return $resultPage;
    }
    
    //Se cambia estado de la orden y se cancela
    public function cancelOrder($mp_order){   
        try {
            $order = $this->orderRepository->get((int)$mp_order);           
            $order->cancel();   
            $this->logger->info('RegisterPayment - Se cancela orden');     
        } catch(Exception $e){
            $this->logger->info('RegisterPayment - Se ha producido un error: '.$e->getMessage());
        }
    }
}
