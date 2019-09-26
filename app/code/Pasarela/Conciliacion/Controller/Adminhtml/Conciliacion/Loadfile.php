<?php

/**
 * Conciliacion Admin Cagegory Map Record Loadfile Controller.
 * @category  Pasarela
 * @package   Pasarela_Conciliacion
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2016 Pasarela Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Pasarela\Conciliacion\Controller\Adminhtml\Conciliacion;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use \Psr\Log\LoggerInterface;
 
class Loadfile extends Action
{
    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';

	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';

	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';

	const TIMEOUT = 'trax_general/catalogo_retailer/timeout';

	const ERRORES = 'trax_general/catalogo_retailer/errores';

    const ORDENES_REINTENTOS = 'trax_ordenes/ordenes_general/pagos_reintentos';

    const ORDENES_CORREO = 'trax_ordenes/ordenes_general/pagos_correo';

    const INVENTARIO_REINTENTOS = 'trax_ordenes/ordenes_general/inventario_reintentos';

    const INVENTARIO_CORREO = 'trax_ordenes/ordenes_general/inventario_correo';

    const CANCELAR_REINTENTOS = 'trax_general/ordenes_general/cancelar_reintentos';

    const CANCELAR_CORREO = 'trax_general/ordenes_general/cancelar_correo';

    const SANDBOX_PRIVATE_KEY = 'payment/pasarela_bancomer/sandbox_private_key';

    const PRODUCCION_PRIVATE_KEY = 'payment/pasarela_bancomer/live_private_key';

    const SANDBOX = 'payment/pasarela_bancomer/is_sandbox';
    
    private $helper;

    public $processed_payments;

    public $processed_orders;

    public $unprocessed_orders;
    
    protected $scopeConfig;

    protected $fileSystem;
 
    protected $uploaderFactory;
 
    protected $allowedExtensions = ['csv']; // to allow file upload types 
 
    protected $fileId = 'conciliation_file'; // name of the input file box  
 
    public function __construct(
        LoggerInterface $logger,
        Action\Context $context,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
        \Magento\Framework\File\Csv $csv,
        \Pasarela\Bancomer\Model\BancomerTransaccionesFactory $bancomerTransacciones,
        \Pasarela\Conciliacion\Model\ConciliacionFactory $conciliacion,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Trax\Catalogo\Helper\Email $email,
        \Trax\Ordenes\Model\IwsOrderFactory $iwsOrder,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService
    ) {
        $this->logger = $logger;
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->csv = $csv;
        $this->_bancomerTransacciones = $bancomerTransacciones;
        $this->_conciliacion = $conciliacion;
        $this->orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $email;
        $this->_iwsOrder = $iwsOrder;
        $this->processed_payments = 0;
        $this->processed_orders = '';
        $this->unprocessed_orders = '';
        parent::__construct($context);
    }
 
    public function execute()
    {
        $destinationPath = $this->getDestinationPath();
 
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $this->fileId])
                ->setAllowCreateFolders(true)
                ->setAllowedExtensions($this->allowedExtensions);
            $result = $uploader->save($destinationPath);   
            if (!$result) {
                $this->messageManager->addError(
                    __('File cannot be saved to path: '.$destinationPath)
                );
                $this->logger->info('BANCOMER CONCILIACION - Error al cargar el archivo en la ruta: '.$destinationPath);
            } else {
                $this->logger->info('BANCOMER CONCILIACION - Se carga el archivo: '.$this->getFilePath($destinationPath, $result['file']));
                $this->validateFile($this->getFilePath($destinationPath, $result['file']));
            } 
            $this->messageManager->addSuccess(
                __('Se cargo el archivo de conciliación')
            );
            $this->_redirect('pasarela_conciliacion/conciliacion/index');
            return;
        } catch (\Exception $e) {
            $this->logger->info('BANCOMER CONCILIACION - '.$e->getMessage());
        }
    }
    
    public function validateFile($filePath)
    {
        $this->logger->info('BANCOMER CONCILIACION - entra a función: '.$filePath);
        $fila = 1;
        if (($gestor = fopen($filePath, "r")) !== FALSE) {
            while (($datos = fgetcsv($gestor, 1000, ";")) !== FALSE) {
                $numero = count($datos);
                $this->logger->info('BANCOMER CONCILIACION - '.$numero.' de campos en la línea '.$fila);
                $this->savePayment($datos);
                $fila++;
            }
            fclose($gestor);
            $this->saveConciliation();
        }

    }
 
    public function saveConciliation()
    {
		$model = $this->_conciliacion->create();
		$model->addData([
			"procesed_payments" => $this->processed_payments,
			"procesed_orders" => $this->processed_orders,
			"unprocesed_orders" => $this->unprocessed_orders
            ]);
        try{
            $saveData = $model->save();
            if($saveData){
                $this->logger->info('BANCOMER CONCILIACION - Se inserto información de la conciliación');
            } else {
                $this->logger->info('BANCOMER CONCILIACION - Se produjo un error al guardar la información de la conciliación');
            }
        } catch (\Exception $e){
            $this->logger->info('BANCOMER CONCILIACION - error al guardar modelo '.$e->getMessage());
        }
    }
 
    public function getDestinationPath()
    {
        return $this->fileSystem
            ->getDirectoryWrite(DirectoryList::TMP)
            ->getAbsolutePath('/');
    }

    public function getFilePath($path, $fileName)
    {
        return rtrim($path, '/') . '/' . ltrim($fileName, '/');
    }

    //Se guarda información de Pago en tabla custom
    public function saveOrderPayment($data) 
    {
		$model = $this->_bancomerTransacciones->create();
        $this->logger->info('BANCOMER CONCILIACION - llama modelo');
		$model->addData([
			"order_id" => $data[8],
			"reference" => $data[7],
			"payment_method" => $data[6],
			"payment_method_code" => $data[6],
			"card_type" => $data[12],
			"bank_name" => $data[18],
			"bank_account" => $data[12],
			"bank_code" => $data[18],
			"sale_id" => $data[10],
			"response" => 'N/A',
			"response_msg" => 'N/A',
			"authorization" => $data[10],
			"date" => new \DateTime($data[0])
            ]);
        try{
            $saveData = $model->save();
            if($saveData){
                $this->logger->info('BANCOMER CONCILIACION - Se inserto información de pago de la orden: '.$mp_reference);
            } else {
                $this->logger->info('BANCOMER CONCILIACION - Se produjo un error al guardar la información de pago de la orden: '.$mp_reference);
            }
        } catch (\Exception $e){
            $this->logger->info('BANCOMER CONCILIACION - error al guardar modelo '.$e->getMessage());
        }
    }
    
    //Se cambia estado de la orden y se genera factura
    public function savePayment($data){   
        try {
            $order = $this->orderRepository->get((int)$data[8]);
            $this->logger->info('BANCOMER CONCILIACION - Orden '.$data[7].' valor pendiente '.$order->getTotalDue());
            if($order->getTotalDue()!=0){
                $this->saveOrderPayment($data);
                $status = \Magento\Sales\Model\Order::STATE_PROCESSING;
                $order->setState($status)->setStatus($status);
                $order->setTotalPaid((float)$data[12]);  
                $order->addStatusHistoryComment("Pago recibido exitosamente")->setIsCustomerNotified(true);            
                $order->save();        
        
                $this->logger->info('BANCOMER CONCILIACION - registra información de la orden');
                $invoice = $this->_invoiceService->prepareInvoice($order);        
                $invoice->setTransactionId($data[10]);          
                $invoice->pay()->save();
        
                $payment = $order->getPayment();                                
                $payment->setAmountPaid($data[12]);
                $payment->setIsTransactionPending(false);
                $payment->save();
                
                $this->logger->info('BANCOMER CONCILIACION - Se registra información de pago en magento');
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                //Se obtienen parametros de configuración por Store        
                $store = $storeManager->getStore($order->getStoreId());
                $storeManager->setCurrentStore($store->getCode());
                //Se obtienen parametros de configuración por Store        
                $configData = $this->getConfigParams($storeScope, $store->getCode()); 
                $this->logger->info('BANCOMER CONCILIACION - Se obtienen parámetros de configuración');
                $serviceUrl = $this->getServiceUrl($configData, 'registerpayments');   
                $this->logger->info('BANCOMER CONCILIACION - url '.$serviceUrl);
                if($serviceUrl){
                    try{
                        $payload = $this->loadPayloadService($data, $store->getWebsiteCode());
                        if($payload){
                            $this->beginRegisterPayments($data[8], $configData, $payload, $serviceUrl, $order, $store->getCode(), 0);
                            $this->processed_payments = $this->processed_payments + 1;
                            if($this->processed_orders == ''){
                                $this->processed_orders = $data[7];
                            } else {
                                $this->processed_orders = $this->processed_orders.', '.$data[7];
                            }
                        } else{
                            $this->logger->info('BANCOMER CONCILIACION - Se ha producido un error al cargar la información de la orden en iws');
                            $this->helper->notify('Soporte Trax', $configData['pagos_correo'], $configData['pagos_reintentos'], $serviceUrl, $payload, $store->getCode());
                        }
                    } catch(Exception $e){
                        $this->logger->info('BANCOMER CONCILIACION - Se ha producido un error: '.$e->getMessage());
                    }
                } else{
                    $this->logger->info('BANCOMER CONCILIACION - Se ha producido un error al conectarse al servicio. No se detectaron parametros de configuracion');
                }
            } else{
                $this->logger->info('BANCOMER CONCILIACION - No procesado: '.$this->unprocessed_orders);
                if($this->unprocessed_orders == ''){
                    $this->unprocessed_orders = $data[7];
                } else {
                    $this->unprocessed_orders = $this->unprocessed_orders.', '.$data[7];
                }
            }
        } catch(Exception $e){
            $this->logger->info('BANCOMER CONCILIACION - Se ha producido un error: '.$e->getMessage());
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
        $configData['pagos_reintentos'] = $this->scopeConfig->getValue(self::ORDENES_REINTENTOS, $storeScope, $websiteCode);
        $configData['pagos_correo'] = $this->scopeConfig->getValue(self::ORDENES_CORREO, $storeScope, $websiteCode);
        $configData['inventario_reintentos'] = $this->scopeConfig->getValue(self::INVENTARIO_REINTENTOS, $storeScope, $websiteCode);
        $configData['inventario_correo'] = $this->scopeConfig->getValue(self::INVENTARIO_CORREO, $storeScope, $websiteCode);
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

    //Load Payload request
	public function loadPayloadService($data, $storeCode) 
	{   
        //Load IWS Order id
        $iwsOrder = $this->loadIwsOrder($data[8]);
        $PaymentTypeId = $this->loadPaymentMethodId($data[8], $data[6], $storeCode);
        if(!$PaymentTypeId){
            return false;
        }
        if($iwsOrder){
            $payments = array();
            $tempPayment['Amount'] = $data[12];
            $tempPayment['Authorization'] = $data[9];
            $tempPayment['BankName'] = $data[18];
            $tempPayment['BankAccount'] = $data[11];
            $tempPayment['PaymentTypeId'] = $PaymentTypeId;
            $tempPayment['Partial'] = false;
            $payments[] = $tempPayment;
            $payload = array(
                'OrderNumber' => $iwsOrder,
                'Payments' => $payments
            );
            $this->logger->info('BANCOMER CONCILIACION - payload: '.json_encode($payload));
            return json_encode($payload);
        }
        return false;
    }

    //Función recursiva para intentos de conexión
    public function beginRegisterPayments($mp_order, $configData, $payload, $serviceUrl, $order, $storeCode, $attempts) {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl, $payload, 'BANCOMER CONCILIACION');
        if($data['status']){     
            //Mapear orden de magento con IWS en tabla custom
            $this->addOrderComment($mp_order, 'Se genero información de pago interno en IWS. Pago Interno IWS #'.$data['resp'][0]->PaymentId, 'BANCOMER CONCILIACION');
            $this->initReleaseOrder($mp_order, $configData, $order, $storeCode);
        } else {
            if(strpos((string)$configData['errores'], (string)$data['status_code']) !== false){
                if($configData['pagos_reintentos']>$attempts){
                    $attempts++;
                    $this->logger->info('BANCOMER CONCILIACION - Error conexión: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexión. Se reintenta conexión #'.$attempts.' con el servicio.');
                    sleep($configData['timeout']);
                    $this->beginRegisterPayments($mp_order, $configData, $payload, $serviceUrl, $order, $storeCode, $attempts);
                } else{
                    $this->logger->info('BANCOMER CONCILIACION - Error conexión: '.$serviceUrl);
                    $this->logger->info('BANCOMER CONCILIACION - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['pagos_correo']);
                    $this->helper->notify('Soporte Trax', $configData['pagos_correo'], $configData['pagos_reintentos'], $serviceUrl, $payload, $storeCode);
                }
            }
        }   
    }

    //Función que inicializa el releaseOrder
    public function initReleaseOrder($mp_order, $configData, $order, $storeCode) {
        $releaseServiceUrl = $this->getServiceUrl($configData, 'releaseorder');   
        $this->logger->info('ReleaseOrder - url '.$releaseServiceUrl);
        if($releaseServiceUrl){
            try{
                $releasePayload = $this->loadReleasePayloadService($mp_order);
                if($releasePayload){
                    $this->beginReleaseOrder($mp_order, $configData, $releasePayload, $releaseServiceUrl, $order, $storeCode, 0);
                } else{
                    $this->logger->info('ReleaseOrder - Se ha producido un error al cargar la información de la orden en iws');
                    $this->helper->notify('Soporte Trax', $configData['pagos_correo'], $configData['pagos_reintentos'], $releaseServiceUrl, $releasePayload, $storeManager->getStore()->getCode());
                }
            } catch(Exception $e){
                $this->logger->info('ReleaseOrder - Se ha producido un error: '.$e->getMessage());
            }
            //TODO: Actualizar datos en base de datos con respuesta de IWS
        } else{
            $this->logger->info('ReleaseOrder - Se ha producido un error al conectarse al servicio. No se detectaron parametros de configuracion');
        }
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

    //Función recursiva para intentos de conexión
    public function beginReleaseOrder($mp_order, $configData, $payload, $serviceUrl, $order, $storeCode, $attempts) {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl, $payload, 'ReleaseOrder');
        if($data['status']){     
            if($data['resp']->OnHold){
                $this->addOrderComment($mp_order, 'Se ha producido un error al ejecutar el método releaseOrder.', 'ReleaseOrder');
            } else {
                $this->addOrderComment($mp_order, 'Se ejecuto el método releaseOrder correctamente.', 'ReleaseOrder');
            }
            //Mapear orden de magento con IWS en tabla custom
        } else {
            if(strpos((string)$configData['errores'], (string)$data['status_code']) !== false){
                if($configData['inventario_reintentos']>$attempts){
                    $attempts++;
                    $this->logger->info('ReleaseOrder - Error conexión: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexión. Se reintenta conexión #'.$attempts.' con el servicio.');
                    sleep($configData['timeout']);
                    $this->beginReleaseOrder($mp_order, $configData, $payload, $serviceUrl, $order, $storeCode, $attempts);
                } else{
                    $this->logger->info('ReleaseOrder - Error conexión: '.$serviceUrl);
                    $this->logger->info('ReleaseOrder - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['inventario_correo']);
                    $this->helper->notify('Soporte Trax', $configData['inventario_correo'], $configData['inventario_reintentos'], $serviceUrl, $payload, $storeCode);
                }
            }
        }   
    }

    //Load Payload request
	public function loadReleasePayloadService($mp_order) 
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

    //Se carga servicio por CURL
	public function loadIwsService($serviceUrl, $payload, $method) 
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
    public function addOrderComment($orderId, $comment, $method) 
    {
		try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $order->addStatusHistoryComment($comment);
            $order->save();
        } catch (\Exception $e) {
            $this->logger->info($method.' - Error al guardar comentario en orden con ID: '.$orderId);
        }
	}
}