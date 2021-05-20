<?php

namespace Intcomex\EventsObservers\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
use Trax\Ordenes\Model\IwsOrderFactory;

class RegisterPayment extends AbstractHelper
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

    const CANCELAR_REINTENTOS = 'trax_ordenes/ordenes_general/cancelar_reintentos';

    const CANCELAR_CORREO = 'trax_ordenes/ordenes_general/cancelar_correo';

    const SANDBOX_PRIVATE_KEY = 'payment/pasarela_bancomer/sandbox_private_key';

    const PRODUCCION_PRIVATE_KEY = 'payment/pasarela_bancomer/live_private_key';

    const SANDBOX = 'payment/pasarela_bancomer/is_sandbox';

    /**
    * @var helper
    */
    protected $helper;

    /**
	* @var \Magento\Framework\App\Config\ScopeConfigInterface
	*/
    protected $scopeConfig;

    /**
     * @var logger
     */
    protected $logger;

    /**
     *  @var _iwsOrder
     */
    protected $_iwsOrder;
    
	/**
     * 
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Trax\Catalogo\Helper\Email $email
     */
    public function __construct(
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Trax\Catalogo\Helper\Email $email,
            \Trax\Ordenes\Model\IwsOrderFactory $iwsOrder
    ) {
        $this->scopeConfig = $scopeConfig;        
        $this->helper = $email;
        $this->_iwsOrder = $iwsOrder;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/events_sales_order.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
    }	
   
     //Función recursiva para intentos de conexión
     public function beginRegisterPayment($mp_order, $configData, $payload, $serviceUrl, $order, $storeCode, $attempts) {
        //Se conecta al servicio 
        $this->logger->info('loadIwsService - Inicio'); 
        
        // se obtiene el iws order
        $iws_idorder = $this->getIwsOrderId($order->getIncrementId());

        if(!$iws_idorder){


            $data = $this->loadIwsService($serviceUrl, $payload, 'RegisterPayment');
            
            
            if($data['status']){     
                //Mapear orden de magento con IWS en tabla custom
                $this->addOrderComment($order->getId(), 'Se genero información de pago interno en IWS. Pago Interno IWS #'.$data['resp'][0]->PaymentId, 'RegisterPayment');
                $this->initReleaseOrder($order->getId(), $configData, $order, $storeCode);
            } else {
                if(strpos((string)$configData['errores'], (string)$data['status_code']) !== false){
                    if($configData['pagos_reintentos']>$attempts){
                        $attempts++;
                        $this->logger->info('RegisterPayment - Error conexión: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexión. Se reintenta conexión #'.$attempts.' con el servicio.');
                        sleep($configData['timeout']);
                        $this->beginRegisterPayment($mp_order, $configData, $payload, $serviceUrl, $order, $storeCode, $attempts);
                    } else{
                        $this->logger->info('RegisterPayment - Error conexión: '.$serviceUrl);
                        $this->logger->info('RegisterPayment - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['pagos_correo']);
                        $this->helper->notify('Soporte Trax', $configData['pagos_correo'], $configData['pagos_reintentos'], $serviceUrl, $payload, $storeCode);
                    }
                }
            }  
        }else{
            $this->logger->info('RegisterPayment - Para la orden: '.$order->getIncrementId().', ya existe un RegisterPayment en Trax con el id:'.$iws_idorder);
        }

        $this->logger->info('loadIwsService - Fin');

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
        if ($status_code == '200'){
            $response = array(
                'status' => true,
                'resp' => json_decode($resp)
            );
        } else {
            $this->logger->info($method.' - curl errors: '.$curl_errors);
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
        $this->logger->info('Se instancio _iwsOrder');            
        $orders->getResource()
            ->load($orders, $mp_order, 'order_id');
        $this->logger->info('Se abre el recurso para _iwsOrder');            
        if($orders->getId()){
            return $orders->getIwsOrder();
        }
        else
            $this->logger->info('No se encontro la orden IWS asociada a la orden de magento');            
        return false;
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
	public function loadPayloadService($mp_order, $mp_amount, $mp_bankname, $mp_authorization, $mp_pan, $mp_paymentMethod, $storeCode) 
	{   
        //Load IWS Order id
        $this->logger->info('Load IWS Order id Start');
        $iwsOrder = $this->loadIwsOrder($mp_order);
        $this->logger->info('Load IWS Order id ' . $iwsOrder . ' End');
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

    //Se carga relación de metodos de pago con trax
    public function loadPaymentMethodId($mp_order, $mp_paymentMethod, $storeCode)
    {   
        $order = $this->loadOrderInformation($mp_order);
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $shipping = $order->getShippingAddress();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('trax_match_payment'); 
		//Select Data from table
        $sql = "Select * FROM " . $tableName." where payment_type='".$method->getTitle()."' AND payment_code='".$mp_paymentMethod."' AND country_code='".$shipping->getCountryId()."' AND store_code='".$storeCode."'";
        $this->logger->info('loadPaymentMethodId: '. $sql);
        $trax = $connection->fetchAll($sql); 
        foreach ($trax as $key => $data) {
            return $data['trax_code'];
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

    /*
    * Return id IWS order
    * @author Johan Martinez
    * @param $order_id  Id order Magento
    * @return int id IWS
    */
    public function getIwsOrderId($order_id){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('iws_order');
        //Select Data from table
        $sql = "Select * FROM " . $tableName." where order_increment_id='".$order_id."' AND register_payment = 0";

        $this->logger->info('RegisterPayment - iws_order sql '.$sql);

        if(!$connection){
            $this->logger->info('RegisterPayment - No hay conexion a la tabla  iws_order');
            return false;
        }else {
            $trax = $connection->fetchAll($sql);

            $this->logger->info('RegisterPayment - iws_order result '.json_encode($trax));

            $mp_order = 0;
            foreach ($trax as $key => $data) {
                $mp_order = $data['iws_order'];
            }
            $this->logger->info('RegisterPayment - Order IWS '.$mp_order);

            if($mp_order!=0) {
                return $mp_order;
            }else{
                return false;
            }
        }
    }


   
}