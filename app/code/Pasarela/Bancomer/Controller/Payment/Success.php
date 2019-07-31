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
use Magento\Framework\Controller\ResultFactory;
use Trax\Ordenes\Model\IwsOrderFactory;
use Pasarela\Grid\Model\GridFactory;

/**
 * Webhook class  
 */
class Success extends \Magento\Framework\App\Action\Action
{

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';

	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';

	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';

    const ORDENES_REINTENTOS = 'trax_general/ordenes_general/pagos_reintentos';

    const ORDENES_CORREO = 'trax_general/ordenes_general/pagos_correo';
    
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
     * @var \Pasarela\Grid\Model\GridFactory
     */
    private $gridFactory;
    
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
     * @param \Pasarela\Grid\Model\GridFactory $gridFactory
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
            \Trax\Ordenes\Model\IwsOrderFactory $iwsOrder,
            \Pasarela\Grid\Model\GridFactory $gridFactory
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
        $this->gridFactory = $gridFactory;
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
            $mp_order = "54";
            $mp_reference = "2000000085";
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
            if($mp_signature == $mp_signature1){
                if($mp_response=='00'){
                    //TODO: Actualizar datos en base de datos
                    $this->saveOrderPayment($mp_order, $mp_reference, $mp_paymentMethod, $mp_cardType, $mp_response, $mp_responsemsg, $mp_authorization, $mp_date, $mp_paymentMethodCode, $mp_bankname, $mp_bankcode, $mp_saleid, $mp_pan);
                    //TODO: Cambiar estado de orden y actualizar información de pago                    
                    $this->changeOrderStatus($mp_order, $mp_amount, $mp_bankname, $mp_saleid, $mp_pan, $mp_authorization, $mp_paymentMethod);
                    $resultPage->getLayout()->getBlock('bancomer_success')->setTitle("Transacción Exitosa");
                } 
                if($mp_response != '0'){
                    //TODO: Cancelar orden
                    $this->cancelOrder($mp_order);
                    $resultPage->getLayout()->getBlock('bancomer_success')->setTitle("Transacción Cancelada");
                } 
            } else{
                $resultPage->getLayout()->getBlock('bancomer_success')->setTitle("Transacción Cancelada");
            }            
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

    //Verifica si el código de la transacción es valido
    public function checkResponse($storeScope, $websiteCode) 
    {
        $enviroment = $this->scopeConfig->getValue(self::SANDBOX, $storeScope, $websiteCode);
        //Se valida entorno para obtener url del servicio
        if($enviroment == '1'){
            $configData['url'] = $this->scopeConfig->getValue(self::URL_SANDBOX, $storeScope, $websiteCode);
            $configData['merchant_id'] = $this->scopeConfig->getValue(self::SANDBOX_MERCHANT_ID, $storeScope, $websiteCode);
            $configData['secret_key'] = $this->scopeConfig->getValue(self::SANDBOX_LLAVE_SECRETA, $storeScope, $websiteCode);
            $configData['public_key'] = $this->scopeConfig->getValue(self::SANDBOX_LLAVE_PUBLICA, $storeScope, $websiteCode);
        } else{
            $configData['url'] = $this->scopeConfig->getValue(self::URL_PRODUCCION, $storeScope, $websiteCode);
            $configData['merchant_id'] = $this->scopeConfig->getValue(self::PRODUCCION_MERCHANT_ID, $storeScope, $websiteCode);
            $configData['secret_key'] = $this->scopeConfig->getValue(self::PRODUCCION_LLAVE_SECRETA, $storeScope, $websiteCode);
            $configData['public_key'] = $this->scopeConfig->getValue(self::PRODUCCION_LLAVE_PUBLICA, $storeScope, $websiteCode);
        }
            $configData['public_key'] = $this->scopeConfig->getValue(self::PRODUCCION_LLAVE_PUBLICA, $storeScope, $websiteCode);
        return $configData;

    }

    //Se guarda información de Pago en tabla custom
    public function saveOrderPayment($mp_order, $mp_reference, $mp_paymentMethod, $mp_cardType, $mp_response, $mp_responsemsg, $mp_authorization, $mp_date, $mp_paymentMethodCode, $mp_bankname, $mp_bankcode, $mp_saleid, $mp_pan) 
    {
		$model = $this->_bancomerTransacciones->create();
		$model->addData([
			"order_id" => $mp_order,
			"reference" => $mp_reference,
			"payment_method" => $mp_paymentMethod,
			"payment_method_code" => $mp_paymentMethodCode,
			"card_type" => $mp_cardType,
			"bank_name" => $mp_bankname,
			"bank_account" => $mp_pan,
			"bank_code" => $mp_bankcode,
			"sale_id" => $mp_saleid,
			"response" => $mp_response,
			"response_msg" => $mp_responsemsg,
			"authorization" => $mp_authorization,
			"date" => $mp_date
			]);
        $saveData = $model->save();
        if($saveData){
            $this->logger->info('RegisterPayment - Se inserto información de pago de la orden: '.$mp_reference);
        } else {
            $this->logger->info('RegisterPayment - Se produjo un error al guardar la información de pago de la orden: '.$mp_reference);
        }
    }
    
    //Se cambia estado de la orden y se genera factura
    public function changeOrderStatus($mp_order, $mp_amount, $mp_bankname, $mp_saleid, $mp_pan, $mp_authorization, $mp_paymentMethod){   
        try {
            $order = $this->orderRepository->get((int)$mp_order);
            $status = \Magento\Sales\Model\Order::STATE_PROCESSING;
            $order->setState($status)->setStatus($status);
            $order->setTotalPaid((float)$mp_amount);  
            $order->addStatusHistoryComment("Pago recibido exitosamente")->setIsCustomerNotified(true);            
            $order->save();        
    
            $invoice = $this->_invoiceService->prepareInvoice($order);        
            $invoice->setTransactionId($mp_saleid);          
            $invoice->pay()->save();
    
            $payment = $order->getPayment();                                
            $payment->setAmountPaid($mp_amount);
            $payment->setIsTransactionPending(false);
            $payment->save();
            
            $this->logger->info('RegisterPayment - Se registra información de pago en magento');
            //TODO: Llamar método registerPayment
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            //Se obtienen parametros de configuración por Store        
            $configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode()); 
            $this->logger->info('RegisterPayment - Se obtienen parámetros de configuración');
            $serviceUrl = $this->getServiceUrl($configData);   
            $this->logger->info('RegisterPayment - url '.$serviceUrl);
            if($serviceUrl){
                try{
                    $payload = $this->loadPayloadService($mp_order, $mp_amount, $mp_bankname, 
                    $mp_authorization, $mp_pan, $mp_paymentMethod, $storeManager->getStore()->getCode());
                    if($payload){
                        $this->beginRegisterPayment($mp_order, $configData, $payload, $serviceUrl, $order, $storeManager->getStore()->getCode(), 0);
                    } else{
                        $this->logger->info('RegisterPayment - Se ha producido un error al cargar la información de la orden en iws');
                        $this->helper->notify('Soporte Trax', $configData['pagos_correo'], $configData['pagos_reintentos'], $serviceUrl, $payload, $storeManager->getStore()->getCode());
                    }
                } catch(Exception $e){
                    $this->logger->info('RegisterPayment - Se ha producido un error: '.$e->getMessage());
                }
                //TODO: Actualizar datos en base de datos con respuesta de IWS
            } else{
                $this->logger->info('RegisterPayment - Se ha producido un error al conectarse al servicio. No se detectaron parametros de configuracion');
            }
        } catch(Exception $e){
            $this->logger->info('RegisterPayment - Se ha producido un error: '.$e->getMessage());
        }
    }
    
    //Se cambia estado de la orden y se cancela
    public function cancelOrder($mp_order){   
        try {
            $order = $this->orderRepository->get((int)$mp_order);
            $status = \Magento\Sales\Model\Order::STATE_CANCELED;
            $order->setState($status)->setStatus($status);
            $order->addStatusHistoryComment("No se ha recibido el pago")->setIsCustomerNotified(true);            
            $order->save();    
            $this->logger->info('RegisterPayment - Se cancela orden'); 
        } catch(Exception $e){
            $this->logger->info('RegisterPayment - Se ha producido un error: '.$e->getMessage());
        }
    }

    //Obtiene los parámetros de configuración desde el cms
    public function getConfigParams($storeScope, $websiteCode) 
    {

        //Se obtienen parametros de configuración por Store
        $configData['apikey'] = $this->scopeConfig->getValue(self::API_KEY, $storeScope, $websiteCode);
        $configData['accesskey'] = $this->scopeConfig->getValue(self::ACCESS_KEY, $storeScope, $websiteCode);
        $enviroment = $this->scopeConfig->getValue(self::ENVIROMENT, $storeScope, $websiteCode);
        //Se valida entorno para obtener url del servicio
        if($enviroment == '0'){
            $configData['url'] = $this->scopeConfig->getValue(self::URL_DESARROLLO, $storeScope, $websiteCode);
        } else{
            $configData['url'] = $this->scopeConfig->getValue(self::URL_PRODUCCION, $storeScope, $websiteCode);
        }
        $configData['pagos_reintentos'] = $this->scopeConfig->getValue(self::ORDENES_REINTENTOS, $storeScope, $websiteCode);
        $configData['pagos_correo'] = $this->scopeConfig->getValue(self::ORDENES_CORREO, $storeScope, $websiteCode);
        return $configData;

    }
 
    //Obtiene url de conexión del servicio
	public function getServiceUrl($configData) 
	{
        if($configData['apikey'] == ''){
            $serviceUrl = false;
        } else {
            $utcTime = gmdate("Y-m-d").'T'.gmdate("H:i:s").'Z';
            $signature = $configData['apikey'].','.$configData['accesskey'].','.$utcTime;
            $signature = hash('sha256', $signature);
            $serviceUrl = $configData['url'].'registerpayments?locale=en&apiKey='.$configData['apikey'].'&utcTimeStamp='.$utcTime.'&signature='.$signature; 
        }
        return $serviceUrl;
    }

    //Función recursiva para intentos de conexión
    public function beginRegisterPayment($mp_order, $configData, $payload, $serviceUrl, $order, $storeCode, $attempts) {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl, $payload);
        if($data){     
            //Mapear orden de magento con IWS en tabla custom
            $this->addOrderComment($mp_order, $data[0]->PaymentId);
        } else {
            if($configData['pagos_reintentos']>$attempts){
                $this->logger->info('RegisterPayment - Error conexión: '.$serviceUrl);
                $this->logger->info('RegisterPayment - Se reintenta conexión #'.$attempts.' con el servicio: '.$serviceUrl);
                $this->beginPlaceOrder($mp_order, $configData, $payload, $serviceUrl, $order, $storeCode, $attempts+1);
            } else{
                $this->logger->info('RegisterPayment - Error conexión: '.$serviceUrl);
                $this->logger->info('RegisterPayment - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['pagos_correo']);
                $this->helper->notify('Soporte Trax', $configData['pagos_correo'], $configData['pagos_reintentos'], $serviceUrl, $payload, $storeCode);
            }
        }   

    }

    //Se carga servicio por CURL
	public function loadIwsService($serviceUrl, $payload) 
	{        
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl,
            CURLOPT_POSTFIELDS => $payload
        ));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload))
        );
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($curl);
        curl_close($curl);    
        $this->logger->info('RegisterPayment - payload: '.$payload);
        $this->logger->info('RegisterPayment - status code: '.$status_code);
        $this->logger->info('RegisterPayment - '.$serviceUrl);
        $this->logger->info('RegisterPayment - curl errors: '.$curl_errors);
        if ($status_code == '200'){
            return json_decode($resp);
        }
        return false;

    }

    //Laod Payload request
	public function loadPayloadService($mp_order, $mp_amount, $mp_bankname, $mp_authorization, $mp_pan, $mp_paymentMethod, $storeCode) 
	{   
        //Load IWS Order id
        $iwsOrder = $this->loadIwsOrder($mp_order);
        $PaymentTypeId = $this->loadPaymentMethodId($mp_order, $mp_paymentMethod, $storeCode);
        if(!$PaymentTypeId){
            return false;
        }
        if($iwsOrder){
            $payments = array();
            $tempPayment['Amount'] = $mp_amount;
            $tempPayment['Authorization'] = $mp_authorization;
            $tempPayment['BankName'] = $mp_bankname;
            $tempPayment['BankAccount'] = $mp_pan;
            $tempPayment['PaymentTypeId'] = $PaymentTypeId;
            $tempPayment['Partial'] = false;
            $payments[] = $tempPayment;
            $payload = array(
                'OrderNumber' => $iwsOrder,
                'Payments' => $payments
            );
            $this->logger->info('RegisterPayment - payload: '.json_encode($payload));
            return json_encode($payload);
        }
        return false;
    }

    //Load IWS ORder for custom model
    public function loadIwsOrder($mp_order)
    {    
        $orders = $this->_iwsOrder->create();
        $orders->getResource()
            ->load($orders, $mp_order, 'order_id');
        if($orders->getId()){
            return $orders->getIwsOrder();
        }
        return false;

    }

    //Se carga relación de metodos de pago con trax
    public function loadPaymentMethodId($mp_order, $mp_paymentMethod, $storeCode)
    {   
        $order = $this->loadOrderInformation($mp_order);
        $payment = $order->getPayment();
        $shipping = $order->getShippingAddress();
        $trax = $this->gridFactory->create();
        $trax->getCollection()
            ->addFieldToFilter('payment_code', $mp_paymentMethod)
            ->addFieldToFilter('payment_type', $method->getTitle())
            ->addFieldToFilter('country_code', $shipping->getCountryId())
            ->addFieldToFilter('store_code', $storeCode);
        if($trax->getId()){
            return $trax->getTraxCode();
        }
        return false;
    }

    //Se añade comentario interno a orden
    public function loadOrderInformation($orderId) 
    {
		try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            return $order;
        } catch (\Exception $e) {
            $this->logger->info('RegisterPayment - Error al obtener información de la orden con ID: '.$orderId);
        }
	}

    //Se añade comentario interno a orden
    public function addOrderComment($orderId, $paymentId) 
    {
		try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $order->addStatusHistoryComment('Se genero información de pago interno en IWS. Pago Interno IWS #'.$paymentId);
            $order->save();
        } catch (\Exception $e) {
            $this->logger->info('RegisterPayment - Error al guardar comentario en orden con ID: '.$orderId);
        }
	}
}
