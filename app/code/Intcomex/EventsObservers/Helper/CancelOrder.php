<?php

namespace Intcomex\EventsObservers\Helper;
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
class CancelOrder extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';

	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';

	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';

	const TIMEOUT = 'trax_general/catalogo_retailer/timeout';

	const ERRORES = 'trax_general/catalogo_retailer/errores';

    const CANCELAR_REINTENTOS = 'trax_ordenes/ordenes_general/cancelar_reintentos';

    const CANCELAR_CORREO = 'trax_ordenes/ordenes_general/cancelar_correo';

    const SANDBOX_PRIVATE_KEY = 'payment/pasarela_bancomer/sandbox_private_key';

    const PRODUCCION_PRIVATE_KEY = 'payment/pasarela_bancomer/live_private_key';

    const SANDBOX = 'payment/pasarela_bancomer/is_sandbox';
    
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
        $this->logger->info('paymenterror - Entra a la pagina de error');   
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->initMessages();          
        try {               
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            //Se obtienen parametros de configuración por Store
            $configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode());
            $mp_order = $_REQUEST['mp_order'];
            $mp_reference = $_REQUEST['mp_reference'];
            $mp_amount = $_REQUEST['mp_amount'];
            $mp_response = $_REQUEST['mp_response'];
            $mp_authorization = $_REQUEST['mp_authorization'];
            $mp_paymentMethod = $_REQUEST['mp_paymentMethod'];
            $mp_cardType = $_REQUEST['mp_cardType'];
            $mp_responsemsg = $_REQUEST['mp_responsemsg'];
            $mp_date = $_REQUEST['mp_date'];
            $mp_paymentMethodCode = $_REQUEST['mp_paymentMethodCode'];
            $mp_signature = $_REQUEST['mp_signature'];
            $mp_bankname = $_REQUEST['mp_bankname'];
            $mp_bankcode = $_REQUEST['mp_bankcode'];
            $mp_pan = $_REQUEST['mp_pan'];
            $mp_saleid = $_REQUEST['mp_saleid'];
            $cadena = $mp_order.$mp_reference.$mp_amount.$mp_authorization;
            $mp_signature1 = hash_hmac('sha256', $cadena, $configData['private_key']);
            /*$mp_order = "56";
            $mp_reference = "2000000087";
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
            $mp_signature1 = hash('sha256', $mp_order.$mp_reference.$mp_amount.'.00'.$mp_authorization);*/
            //TODO: Cancelar orden
            $this->cancelIwsOrder($mp_order);
            $this->cancelOrder($mp_order);
            $resultPage->getLayout()->getBlock('bancomer_error')->setTitle("Transacción Cancelada");         
            $resultPage->getLayout()->getBlock('bancomer_error')->setOrder($mp_order);
            $resultPage->getLayout()->getBlock('bancomer_error')->setReference($mp_reference);
            $resultPage->getLayout()->getBlock('bancomer_error')->setAmount($mp_amount);
            $resultPage->getLayout()->getBlock('bancomer_error')->setPaymentMethod($mp_paymentMethod);
            $resultPage->getLayout()->getBlock('bancomer_error')->setResponse($mp_responsemsg);
        } catch (\Exception $e) {
            $this->logger->error('#SUCCESS', array('message' => $e->getMessage(), 'code' => $e->getCode(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()));
            $resultPage->getLayout()->getBlock('bancomer_error')->setTitle("Error");
        }        
        return $resultPage;
    }
    
    //Se cancela orden en IWS
    public function cancelIwsOrder($mp_order){   
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        //Se obtienen parametros de configuración por Store        
        $configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode()); 
        $this->logger->info('CancelOrder - Se obtienen parámetros de configuración');
        $serviceUrl = $this->getServiceUrl($configData, 'cancelorder');   
        $this->logger->info('CancelOrder - url '.$serviceUrl);
        if($serviceUrl){
            try{
                $payload = $this->loadPayloadService($mp_order);
                if($payload){
                    $this->beginCancelOrder($mp_order, $configData, $payload, $serviceUrl, $storeManager->getStore()->getCode(), 0);
                } else{
                    $this->logger->info('CancelOrder - Se ha producido un error al cargar la información de la orden en iws');
                    $this->helper->notify('Soporte Trax', $configData['cancelar_correo'], $configData['cancelar_reintentos'], $serviceUrl, $payload, $storeManager->getStore()->getCode());
                }
            } catch(Exception $e){
                $this->logger->info('CancelOrder - Se ha producido un error: '.$e->getMessage());
            }
            //TODO: Actualizar datos en base de datos con respuesta de IWS
        } else{
            $this->logger->info('CancelOrder - Se ha producido un error al conectarse al servicio. No se detectaron parametros de configuracion');
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
        $configData['timeout'] = $this->scopeConfig->getValue(self::TIMEOUT, $storeScope, $websiteCode);
        $configData['errores'] = $this->scopeConfig->getValue(self::ERRORES, $storeScope, $websiteCode);
        $configData['cancelar_reintentos'] = $this->scopeConfig->getValue(self::CANCELAR_REINTENTOS, $storeScope, $websiteCode);
        $configData['cancelar_correo'] = $this->scopeConfig->getValue(self::CANCELAR_CORREO, $storeScope, $websiteCode);
        $sandbox = $this->scopeConfig->getValue(self::SANDBOX, $storeScope, $websiteCode);
        //Se valida entorno para obtener url del servicio
        if($sandbox == '1'){
            $configData['private_key'] = $this->scopeConfig->getValue(self::SANDBOX_PRIVATE_KEY, $storeScope, $websiteCode);
        } else{
            $configData['private_key'] = $this->scopeConfig->getValue(self::PRODUCCION_PRIVATE_KEY, $storeScope, $websiteCode);
        }
        return $configData;

    }
 
    //Obtiene url de conexión del servicio
	public function getServiceUrl($configData, $method) 
	{
        if($configData['apikey'] == ''){
            $serviceUrl = false;
        } else {
            $utcTime = gmdate("Y-m-d").'T'.gmdate("H:i:s").'Z';
            $signature = $configData['apikey'].','.$configData['accesskey'].','.$utcTime;
            $signature = hash('sha256', $signature);
            $serviceUrl = $configData['url'].$method.'?locale=en&apiKey='.$configData['apikey'].'&utcTimeStamp='.$utcTime.'&signature='.$signature; 
        }
        return $serviceUrl;
    }

    //Laod Payload request
    public function loadPayloadService($mp_order) 
	{   
        //Load IWS Order id
        $iwsOrder = $this->loadIwsOrder($mp_order);
        if($iwsOrder){
            $payload['OrderNumber'] = $iwsOrder;
            $this->logger->info('ReleaseOrder - payload: '.json_encode($payload));
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

    //Función recursiva para intentos de conexión
    public function beginCancelOrder($mp_order, $configData, $payload, $serviceUrl, $storeCode, $attempts) {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl, $payload, 'CancelOrder');
        if($data['status']){     
            //Mapear orden de magento con IWS en tabla custom
            $this->addOrderComment($mp_order, 'Se cancelo orden interna en IWS. ');
        } else {
            if(strpos((string)$configData['errores'], (string)$data['status_code']) !== false){
                if($configData['cancelar_reintentos']>$attempts){
                    $attempts++;
                    $this->logger->info('CancelOrder - Error conexión: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexión. Se reintenta conexión #'.$attempts.' con el servicio.');
                    sleep($configData['timeout']);
                    $this->beginCancelOrder($mp_order, $configData, $payload, $serviceUrl, $storeCode, $attempts);
                } else{
                    $this->logger->info('CancelOrder - Error conexión: '.$serviceUrl);
                    $this->logger->info('CancelOrder - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['cancelar_correo']);
                    $this->helper->notify('Soporte Trax', $configData['cancelar_correo'], $configData['cancelar_reintentos'], $serviceUrl, $payload, $storeCode);
                }
            }
        }   

    }

    //Se carga servicio por CURL
	public function loadIwsService($serviceUrl, $payload, $method) 
	{        
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl,
            CURLOPT_POST => 1,
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
        $this->logger->info($method.' - status code: '.$status_code);
        $this->logger->info($method.' - '.$serviceUrl);
        $this->logger->info($method.' - curl errors: '.$curl_errors);
        if ($status_code == '200'){
            $response = array(
                'status' => true,
                'resp' => json_decode($resp)
            );
        } else {
            $response = array(
                'status' => false,
                'status_code' => $status_code
            );
        }
        return $response;

    }

    //Se añade comentario interno a orden
    public function addOrderComment($orderId, $comment) 
    {
		try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $order->addStatusHistoryComment($comment);
            $order->save();
        } catch (\Exception $e) {
            $this->logger->info('CancelOrder - Error al guardar comentario en orden con ID: '.$orderId);
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
            $this->logger->info('CancelOrder - Se cancela orden interna de magento');     
        } catch(Exception $e){
            $this->logger->info('CancelOrder - Se ha producido un error: '.$e->getMessage());
        }
    }
}
