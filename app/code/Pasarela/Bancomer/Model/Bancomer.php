<?php
/**
 * Pasarela_Bancomer payment method model
 *
 * @category    Pasarela
 * @package     Pasarela_Bancomer
 * @author      Valentina Aguirre
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
 */

namespace Pasarela\Bancomer\Model;

use Magento\Store\Model\ScopeInterface;

class Bancomer extends \Magento\Payment\Model\Method\AbstractMethod
{

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_catalogo/catalogo_general/apuntar_a';

	const URL_DESARROLLO = 'trax_catalogo/catalogo_general/url_desarrollo';

	const URL_PRODUCCION = 'trax_catalogo/catalogo_general/url_produccion';

    const ORDENES_REINTENTOS = 'trax_catalogo/catalogo_general/ordenes_reintentos';

    const ORDENES_CORREO = 'trax_catalogo/catalogo_general/ordenes_correo';
    
    private $helper;
	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    const CODE = 'pasarela_bancomer';

    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canOrder = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_isOffline = true;
    protected $scope_config;
    protected $openpay = false;
    protected $is_sandbox;
    protected $merchant_id = null;
    protected $sk = null;
    protected $deadline = 72;
    protected $sandbox_merchant_id;
    protected $sandbox_sk;
    protected $live_merchant_id;
    protected $live_sk;
    protected $pdf_url_base;
    protected $show_map;
    protected $supported_currency_codes = array('MXN');
    protected $_transportBuilder;
    protected $logger;
    protected $_storeManager;
    protected $_inlineTranslation;
    protected $_directoryList;
    protected $_file;
    
    /**
     * 
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param TransportBuilder $transportBuilder
     * @param array $data
     */
    public function __construct(
            \Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry, 
            \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
            \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, 
            \Magento\Payment\Helper\Data $paymentData, 
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
            \Magento\Payment\Model\Method\Logger $logger,             
            \Pasarela\Bancomer\Mail\Template\TransportBuilder $transportBuilder,
            \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Psr\Log\LoggerInterface $logger_interface,
            \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
            \Magento\Framework\Filesystem\Io\File $file,
            array $data = []            
    ) {
        parent::__construct(
            $context,
            $registry, 
            $extensionFactory,
            $customAttributeFactory,
            $paymentData, 
            $scopeConfig,
            $logger,
            null,
            null,            
            $data            
        );

        $this->_file = $file;
        $this->_directoryList = $directoryList;
        $this->logger = $logger_interface;
        $this->_inlineTranslation = $inlineTranslation;        
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->scope_config = $scopeConfig;
    }

    /**
     * 
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return \Openpay\Stores\Model\Payment
     * @throws \Magento\Framework\Validator\Exception
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount) {

        /**
         * Magento utiliza el timezone UTC, por lo tanto sobreescribimos este 
         * por la configuración que se define en el administrador         
         */
        $store_tz = $this->scope_config->getValue('general/locale/timezone');
        date_default_timezone_set($store_tz);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        /** @var \Magento\Sales\Model\Order\Address $billing */
        $billing = $order->getBillingAddress();

        try {

            $customer_data = array(
                'name' => $billing->getFirstname(),
                'last_name' => $billing->getLastname(),
                'phone_number' => $billing->getTelephone(),
                'email' => $order->getCustomerEmail()
            );

            if ($this->validateAddress($billing)) {
                $customer_data['address'] = array(
                    'line1' => $billing->getStreetLine(1),
                    'line2' => $billing->getStreetLine(2),
                    'postal_code' => $billing->getPostcode(),
                    'city' => $billing->getCity(),
                    'state' => $billing->getRegion(),
                    'country_code' => $billing->getCountryId()
                );
            }

            $due_date = date('Y-m-d\TH:i:s', strtotime('+ '.$this->deadline.' hours'));

            $charge_request = array(
                'method' => 'store',
                'amount' => $amount,
                'description' => sprintf('ORDER #%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
                'order_id' => $order->getIncrementId(),
                'due_date' => $due_date,
                'customer' => $customer_data
            );
            $state = \Magento\Sales\Model\Order::STATE_NEW;
            $order->setState($state)->setStatus($state);

            //Conectarse con IWS
            $iwsResponse = $this->loadPlaceOrderService($order);
            $order->setExtOrderId($iwsResponse->OrderNumber);
            $this->logger->info('PlaceOrder - Intcomex order number'.$iwsResponse->OrderNumber);
            
        } catch (\Exception $e) {
            $this->debugData(['exception' => $e->getMessage()]);
            $this->_logger->error(__( $e->getMessage()));
            throw new \Magento\Framework\Validator\Exception(__($this->error($e)));
        }

        $payment->setSkipOrderProcessing(true);
        return $this;
    }

    /**
     * @param Address $billing
     * @return boolean
     */
    public function validateAddress($billing) {
        if ($billing->getStreetLine(1) && $billing->getCity() && $billing->getPostcode() && $billing->getRegion() && $billing->getCountryId()) {
            return true;
        }
        return false;
    }
    
    /*
     * Validate if host is secure (SSL)
     */
    public function hostSecure() {
        $is_secure = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $is_secure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $is_secure = true;
        }
        
        return $is_secure;
    }

    //Carga servicio placeorder
    public function loadPlaceOrderService($order){
        
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		//Se obtienen parametros de configuración por Store
		$configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode());
		//Se obtiene lista de sku
        $orderId = $observer->getEvent()->getOrderIds();
        $order = $this->order->load($orderId[0]);      
		//Se obtiene url del servicio
		$serviceUrl = $this->getServiceUrl($configData, $order->getIncrementId());
        //Se carga el servicio por curl
        $data = $this->loadIwsService($serviceUrl, $order, $storeManager->getStore()->getCode());
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
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl,
            CURLOPT_POSTFIELDS => $payload
        ));
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($curl);
        curl_close($curl);    
        $this->logger->info('PlaceOrder- status code: '.$status_code);
        $this->logger->info('PlaceOrder- '.$serviceUrl);
        $this->logger->info('PlaceOrder- curl errors: '.$curl_errors);
        if ($status_code == '200'){
            return json_decode($resp);
        }
        return false;

    }

}
