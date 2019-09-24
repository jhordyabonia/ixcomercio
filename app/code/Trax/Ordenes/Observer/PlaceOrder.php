<?php
namespace Trax\Ordenes\Observer;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use \Psr\Log\LoggerInterface;
use Trax\Ordenes\Model\IwsOrderFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Trax\Grid\Model\GridFactory;

class PlaceOrder implements \Magento\Framework\Event\ObserverInterface
{

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';

	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';

	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';

	const TIMEOUT = 'trax_general/catalogo_retailer/timeout';

	const ERRORES = 'trax_general/catalogo_retailer/errores';

    const ORDENES_REINTENTOS = 'trax_ordenes/ordenes_general/ordenes_reintentos';

    const ORDENES_CORREO = 'trax_ordenes/ordenes_general/ordenes_correo';

    const STORE_ID = 'trax_ordenes/ordenes_general/store_id';

    const PORCENTAJE_IMPUESTO = 'trax_ordenes/ordenes_general/porcentaje_impuesto';
    
    private $helper;
	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

	protected $logger;

    /**
     * @var \Trax\Grid\Model\GridFactory
     */
    private $gridFactory;
	
    /**
     * AdminFailed constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Sales\Model\Order $order,
        LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Trax\Catalogo\Helper\Email $email,
        \Trax\Ordenes\Model\IwsOrderFactory  $iwsOrder,
        \Magento\Framework\Controller\ResultFactory $result,
        \Trax\Grid\Model\GridFactory $gridFactory
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $email;
        $this->order = $order;     
        $this->_iwsOrder = $iwsOrder;
        $this->resultRedirect = $result;
        $this->gridFactory = $gridFactory;
	}
	
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        //Se obtienen parametros de configuración por Store        
		$configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode());
        $this->logger->info('PlaceOrder - Se obtienen parámetros de configuración');
		//Se obtiene lista de sku
        $orderId = $observer->getEvent()->getOrderIds();
        $order = $this->order->load($orderId[0]);      
        $this->logger->info('PlaceOrder - Se inicia llamado para la orden magento '.$order->getIncrementId());
		//Se obtiene url del servicio
		$serviceUrl = $this->getServiceUrl($configData, $order->getIncrementId());
        //Se carga el servicio por curl
        $this->logger->info('PlaceOrder - url '.$serviceUrl);
        try{
            $payload = $this->loadPayloadService($order, $storeManager->getWebsite()->getCode(), $configData['store_id'], $configData['porcentaje_impuesto']);
            if($payload){
                $this->beginPlaceOrder($configData, $payload, $serviceUrl, $order, $storeManager->getStore()->getCode(), 0);
            } else {
                $this->logger->info('PlaceOrder - Se ha producido un error al obtener match con Trax');
                $this->helper->notify('Soporte Trax', $configData['ordenes_correo'], $configData['ordenes_reintentos'], $serviceUrl, $payload, $storeManager->getStore()->getCode());
            }
        } catch(Exception $e){
            $this->logger->info('PlaceOrder - Se ha producido un error: '.$e->getMessage());
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
        $configData['porcentaje_impuesto'] = $this->scopeConfig->getValue(self::PORCENTAJE_IMPUESTO, $storeScope, $websiteCode);
        $configData['ordenes_reintentos'] = $this->scopeConfig->getValue(self::ORDENES_REINTENTOS, $storeScope, $websiteCode);
        $configData['ordenes_correo'] = $this->scopeConfig->getValue(self::ORDENES_CORREO, $storeScope, $websiteCode);
        $configData['store_id'] = $this->scopeConfig->getValue(self::STORE_ID, $storeScope, $websiteCode);
        return $configData;

    }
 
    //Obtiene url de conexión del servicio
	public function getServiceUrl($configData, $orderIncrementId) 
	{
        if($configData['apikey'] == ''){
            $serviceUrl = false;
        } else {
            $utcTime = gmdate("Y-m-d").'T'.gmdate("H:i:s").'Z';
            $signature = $configData['apikey'].','.$configData['accesskey'].','.$utcTime;
            $signature = hash('sha256', $signature);
            $serviceUrl = $configData['url'].'placeorder?locale=en&apiKey='.$configData['apikey'].'&utcTimeStamp='.$utcTime.'&signature='.$signature.'&tag=&customerOrderNumber='.$orderIncrementId.'&generateTokens=false'; 
        }
        return $serviceUrl;
    }

    //Función recursiva para intentos de conexión
    public function beginPlaceOrder($configData, $payload, $serviceUrl, $order, $storeCode, $attempts) {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl, $payload, $storeCode);
        if($data['status']){     
            //Mapear orden de magento con IWS en tabla custom
            $this->saveIwsOrder($data['resp']->OrderNumber, $order->getId(), $order->getIncrementId());
            $this->addOrderComment($order->getId(), $data['resp']->OrderNumber);
        } else {
            if(strpos((string)$configData['errores'], (string)$data['status_code']) !== false){
                if($configData['ordenes_reintentos']>$attempts){
                    $attempts++;
                    $this->logger->info('PlaceOrder - Error conexión: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexión. Se reintenta conexión #'.$attempts.' con el servicio.');
                    sleep($configData['timeout']);
                    $this->beginPlaceOrder($configData, $payload, $serviceUrl, $order, $storeCode, $attempts);
                } else{
                    $this->logger->info('PlaceOrder - Error conexión: '.$serviceUrl);
                    $this->logger->info('PlaceOrder - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['ordenes_correo']);
                    $this->helper->notify('Soporte Trax', $configData['ordenes_correo'], $configData['ordenes_reintentos'], $serviceUrl, $payload, $storeCode);
                }
            }
        }   

    }

    //Se carga servicio por CURL
	public function loadIwsService($serviceUrl, $payload, $storeCode) 
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
        $this->logger->info('PlaceOrder - payload: '.$payload);
        $this->logger->info('PlaceOrder - status code: '.$status_code);
        $this->logger->info('PlaceOrder - '.$serviceUrl);
        $this->logger->info('PlaceOrder - curl errors: '.$curl_errors);
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

    //Se guarda información de IWS en tabla custom
    public function saveIwsOrder($orderNumber, $orderId, $orderIncrementId) 
    {
		$model = $this->_iwsOrder->create();
		$model->addData([
			"order_id" => $orderId,
			"order_increment_id" => $orderIncrementId,
			"iws_order" => $orderNumber,
			]);
        $saveData = $model->save();
        if($saveData){
            $this->logger->info('PlaceOrder - Se inserto la orden de IWS: '.$orderNumber);
        } else {
            $this->logger->info('PlaceOrder - Se produjo un error al guardar la orden de IWS: '.$orderNumber);
        }
	}

    //Laod Payload request
	public function loadPayloadService($order, $storeCode, $configDataStoreId, $configDataImpuesto) 
	{        
        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        $orderItems = $order->getAllItems();
        $coupon = array();
        $shippingAmount = $order->getShippingAmount() - ($order->getShippingAmount() * $configDataImpuesto / 100);
        $this->logger->info('PlaceOrder - Valor envio: '.$order->getShippingAmount().' Valor sin impuesto: '.$shippingAmount.' Porcentaje impuesto: '.$configDataImpuesto);
        if($order->getCouponCode() != '' || $order->getCouponCode() != null){            
            $coupon = array($order->getCouponCode());
        }
        $giftcard = json_decode($order->getGiftCards());
        $giftcardData = "";
        if(count($giftcard)>0){
            if(count($coupon)>0){
                array_push($coupon, $giftcard[0]->c);
            } else{
                $coupon = array($giftcard[0]->c);
            }
        }
        $discount = abs($order->getGiftCardsAmount()) + abs($order->getBaseDiscountAmount());
        $shippingData = $this->loadShippingInformation($order, $shipping->getCountryId(), $storeCode);
        if(!$shippingData['CarrierId']){
            $this->logger->info('PlaceOrder - No se ha obtenido carrier ID');
            return false;
        }
        $items = array();
        foreach ($orderItems as $key => $dataItem) {
            $tempItem['Sku'] = $dataItem->getSku();
            $tempItem['Quantity'] = (int)$dataItem->getQtyOrdered();
            $tempItem['Price'] = $dataItem->getPrice();
            if(count($coupon) == 0){
                $price = $dataItem->getOriginalPrice() - $dataItem->getPrice();
                if($price > 0){
                    $discount = $price;
                }
            }
            $tempItem['Discounts'] = $discount;
            $tempItem['CouponCodes'] = $coupon;
            $tempItem['StoreItemId'] = $dataItem->getId();
            $items[] = $tempItem;
        }
        $payload = array(
            'StoreOrder' => array(
                'StoreId' => $configDataStoreId,
                'StoreOrderNumber' => $order->getIncrementId(),
                'Customer' => array(
                    'FirstName' => $billing->getFirstname(),
                    'LastName' => $billing->getLastname(),
                    'Email' => $billing->getEmail(),
                    'Cellphone' => $billing->getTelephone(),
                    'DocumentId' => $billing->getIdentification(),
                ),
                'Billing' => array(
                    'FirstName' => $billing->getFirstname(),
                    'LastName' => $billing->getLastname(),
                    'Email' => $billing->getEmail(),
                    'DocumentId' => $billing->getIdentification(),
                    'Cellphone' => $billing->getTelephone(),
                    'LandLinePhone' => '',
                    'OtherPhone' => '',
                    'Address' => $billing->getStreetLine(1),
                    'SuiteNumber' => '',
                    'ComplexName' => '',
                    'LocalizationReference' => '',
                    'State' => $billing->getRegion(),
                    'City' => $billing->getCity(),
                    'Neighborhood' => '',
                    'CountryId' => $billing->getCountryId(),
                    'PostalCode' => $billing->getPostalCode(),
                ),
                'Shipping' => array(
                    'FirstName' => $shipping->getFirstname(),
                    'LastName' => $shipping->getLastname(),
                    'Email' => $shipping->getEmail(),
                    'DocumentId' => $shipping->getIdentification(),
                    'Cellphone' => $shipping->getTelephone(),
                    'LandLinePhone' => '',
                    'OtherPhone' => '',
                    'Address' => $shipping->getStreetLine(1),
                    'SuiteNumber' => '',
                    'ComplexName' => '',
                    'LocalizationReference' => '',
                    'State' => $shipping->getRegion(),
                    'City' => $shipping->getCity(),
                    'Neighborhood' => '',
                    'CountryId' => $shipping->getCountryId(),
                    'PostalCode' => $shipping->getPostalCode(),
                ),
                'DeliveryType' => $order->getShippingMethod(),
            ),
            'Discounts' => $discount,
            'CouponCodes' => $coupon,
            'TaxRegistrationNumber' => $billing->getIdentification(),
            'InvoiceRequested' => false,
            'ReceiveInvoiceByMail' => false,
            'Shipments' => array(
                array(
                    'FreightService' => "MiEnvio.mx",
                    'FreightShipmentId' => $order->getQuoteId(),
                    'ServiceType' => $shippingData['ServiceType'],
                    'CarrierId' => $shippingData['CarrierId'],
                    'Amount' => $shippingAmount,
                    'FreightCost' => 0,
                )
            ),
            'Items' => $items
        );
        return json_encode($payload);
    }

    //Se Carga información de carrier
    public function loadShippingInformation($order, $country, $storeCode) 
    {
        $orderShipping = explode(" - ", $order->getShippingDescription());
        $shipping['ServiceType'] = $orderShipping[1];
        $shipping['CarrierId'] = $this->loadCarrierId($country, $orderShipping, $storeCode);     
        return $shipping;
	}

    //Se carga relación de carrier con trax
    public function loadCarrierId($country, $orderShipping, $storeCode)
    {   
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('trax_match_carrier'); 
		//Select Data from table
        $sql = "Select * FROM " . $tableName." where carrier='".$orderShipping[0]."' AND country_code='".$country."' AND store_code='".$storeCode."'";
        $trax = $connection->fetchAll($sql); 
        foreach ($trax as $key => $data) {
            return $data['trax_code'];
        }
        return false;
    }

    //Se añade comentario interno a orden
    public function addOrderComment($orderId, $iwsOrder) 
    {
		try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $order->addStatusHistoryComment('Se genero orden interna en IWS. Orden Interna IWS #'.$iwsOrder);
            $order->setExtOrderId($iwsOrder);
            $order->save();
        } catch (\Exception $e) {
            $this->logger->info('PlaceOrder - Error al guardar comentario en orden con ID: '.$orderId);
        }
	}
}
