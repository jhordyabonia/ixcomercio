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
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Psr\Log\LoggerInterface $logger_interface,
            \Magento\Sales\Model\Service\InvoiceService $invoiceService,
            \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
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
            $mp_order = "47";
            $mp_reference = "2000000078";
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
            if($mp_signature == $mp_signature1){
                echo 'mp_order: '.$mp_order.'<br>mp_reference: '.$mp_reference.'<br>mp_amount: '.$mp_amount.'<br>mp_paymentMethod: '.$mp_paymentMethod.'<br>mp_cardType: '.$mp_cardType.'<br>mp_response: '.$mp_response.'<
                br>mp_responsemsg: '.$mp_responsemsg.'<br>mp_authorization: '.$mp_authorization.'<br>mp_date: '.$mp_date.'<br>mp_paymentMethodCode: '.$mp_paymentMethodCode.'<br>mp_bankname: '.$mp_bankname.'<br>mp_bankcode: '.$mp_bankcode.'<br>mp_saleid: '.$mp_saleid.'<br>mp_pan: '.$mp_pan.'<br>mp_signature: '.$mp_signature. '<br>mp_signature1: '.$mp_signature1;
                //TODO: Actualizar datos en base de datos
                //TODO: Cambiar estado de orden y actualizar información de pago
                //TODO: Llamar método registerPayment
                //TODO: Actualizar datos en base de datos con respuesta de IWS
            } else{
                echo "error";
            }
            echo "success";
            exit();
            
        } catch (\Exception $e) {
            $this->logger->error('#SUCCESS', array('message' => $e->getMessage(), 'code' => $e->getCode(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()));
            //throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }
        
        return $this->resultRedirectFactory->create()->setPath('checkout/cart'); 
    }
}
