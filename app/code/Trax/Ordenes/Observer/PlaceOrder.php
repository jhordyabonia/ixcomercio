<?php
namespace Trax\Ordenes\Observer;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use \Psr\Log\LoggerInterface;

class PlaceOrder implements \Magento\Framework\Event\ObserverInterface
{

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_catalogo/catalogo_retailer/apuntar_a';

	const URL_DESARROLLO = 'trax_catalogo/catalogo_retailer/url_desarrollo';

	const URL_PRODUCCION = 'trax_catalogo/catalogo_retailer/url_produccion';

    const ORDENES_REINTENTOS = 'trax_general/ordenes_general/ordenes_reintentos';

    const ORDENES_CORREO = 'trax_general/ordenes_general/ordenes_correo';
    
    private $helper;
	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

	protected $logger;
	
    /**
     * AdminFailed constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Sales\Model\Order $order,
        LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Trax\Catalogo\Helper\Email $email
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $email;
        $this->order = $order;     
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
            $data = $this->loadIwsService($serviceUrl, $order, $storeManager->getStore()->getCode());
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        } catch(Exception $e){
            echo $e->getMessage();
        }
        exit();
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
        $configData['ordenes_reintentos'] = $this->scopeConfig->getValue(self::ORDENES_REINTENTOS, $storeScope, $websiteCode);
        $configData['ordenes_correo'] = $this->scopeConfig->getValue(self::ORDENES_CORREO, $storeScope, $websiteCode);
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

	public function loadIwsService($serviceUrl, $order, $storeCode) 
	{        
        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        $orderItems = $order->getAllItems();
        $items = array();
        foreach ($orderItems as $key => $dataItem) {
            $tempItem['Sku'] = $dataItem->getSku();
            $tempItem['Quantity'] = $dataItem->getQtyOrdered();
            $tempItem['Price'] = $dataItem->getPrice();
            $tempItem['Discount'] = '';
            $tempItem['CouponCode'] = '';
            $tempItem['StoreItemId'] = $dataItem->getId();
            $items[] = $tempItem;
        }
        $payload = array(
            'StoreOrder' => array(
                'StoreId' => $storeCode,
                'StoreOrderNumber' => $order->getIncrementId(),
                'Customer' => array(
                    'FirstName' => $billing->getFirstname(),
                    'LastName' => $billing->getLastname(),
                    'Email' => $billing->getCustomerEmail(),
                    'Cellphone' => $billing->getTelephone(),
                    'DocumentId' => '1040505',
                ),
                'Billing' => array(
                    'FirstName' => $billing->getFirstname(),
                    'LastName' => $billing->getLastname(),
                    'Email' => $billing->getCustomerEmail(),
                    'DocumentId' => '1040505',
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
                ),
                'Shipping' => array(
                    'FirstName' => $shipping->getFirstname(),
                    'LastName' => $shipping->getLastname(),
                    'Email' => $shipping->getCustomerEmail(),
                    'DocumentId' => '1040505',
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
                ),
                'DeliveryType' => $order->getShippingMethod(),
            ),
            'CouponCodes' => array(),
            'TaxRegistrationNumber' => "64251 2 357348 DV41",
            'InvoiceRequested' => true,
            'ReceiveInvoiceByMail' => true,
            'Shipments' => array(
                'FreightService' => 'mienvio',
                'FreightShipmentId' => '123456789',
                'ServiceType' => $order->getShippingMethod(),
                'CarrierId' => '29491',
                'Amount' => $order->getShippingAmount(),
                'FreightCost' => $order->getShippingAmount(),
            ),
            'Items' => $items
        );
        $payload = json_encode($payload);
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl,
            CURLOPT_POSTFIELDS => $payload
        ));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload))
        );
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($curl);
        curl_close($curl);    
        $this->logger->info('PlaceOrder - status code: '.$status_code);
        $this->logger->info('PlaceOrder - '.$serviceUrl);
        $this->logger->info('PlaceOrder - curl errors: '.$curl_errors);
        if ($status_code == '200'){
            return json_decode($resp);
        }
        return false;

    }
}