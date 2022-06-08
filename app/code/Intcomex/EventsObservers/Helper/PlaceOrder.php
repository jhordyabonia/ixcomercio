<?php

namespace Intcomex\EventsObservers\Helper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use \Magento\Framework\App\Helper\AbstractHelper;
use Trax\Ordenes\Model\IwsOrderFactory;

use \Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Trax\Grid\Model\GridFactory;

class PlaceOrder extends AbstractHelper
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

    const PRODUCTO_IMPUESTO = 'trax_ordenes/ordenes_general/producto_impuesto';
    
    /**
    * @var helper
    */
    private $helper;

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
     * @var \Trax\Grid\Model\GridFactory
     */
    private $gridFactory;

    protected $eavAttributeRepository;

    /**
     * @var \Intcomex\Crocs\Model\ConfigurableProduct
     */
    protected $configurableProduct;

    /**
     * 
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Trax\Catalogo\Helper\Email $email
     */
    public function __construct(
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Trax\Catalogo\Helper\Email $email,
            \Trax\Ordenes\Model\IwsOrderFactory $iwsOrder,
            \Magento\Sales\Model\Order $order,
            LoggerInterface $logger,
            \Magento\Framework\Controller\ResultFactory $result,
            \Trax\Grid\Model\GridFactory $gridFactory,
            \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepositoryInterface,
            \Intcomex\Crocs\Model\ConfigurableProduct $configurableProduct,
            \Intcomex\Credomatic\Helper\DataRule $credoHelper
    ) {
        $this->scopeConfig = $scopeConfig;        
        $this->helper = $email;
        $this->_iwsOrder = $iwsOrder;
        $this->logger = $logger;
        $this->order = $order;     
        $this->resultRedirect = $result;
        $this->gridFactory = $gridFactory;
        $this->eavAttributeRepository = $eavAttributeRepositoryInterface;
        $this->configurableProduct = $configurableProduct;
        $this->credoHelper = $credoHelper;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/events_sales_order.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
    }
    
    //Obtiene los parÃ¡metros de configuraciÃ³n desde el cms
    public function getConfigParams($storeScope, $websiteCode) 
    {

        //Se obtienen parametros de configuraciÃ³n por Store
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
        $configData['producto_impuesto'] = $this->scopeConfig->getValue(self::PRODUCTO_IMPUESTO, $storeScope, $websiteCode);
        $configData['ordenes_reintentos'] = $this->scopeConfig->getValue(self::ORDENES_REINTENTOS, $storeScope, $websiteCode);
        $configData['ordenes_correo'] = $this->scopeConfig->getValue(self::ORDENES_CORREO, $storeScope, $websiteCode);
        $configData['store_id'] = $this->scopeConfig->getValue(self::STORE_ID, $storeScope, $websiteCode);
        return $configData;

    }
 
    //Obtiene url de conexiÃ³n del servicio
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

    //FunciÃ³n recursiva para intentos de conexiÃ³n
    public function beginPlaceOrder($configData, $payload, $serviceUrl, $order, $storeCode, $attempts) {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl, $payload, $storeCode);
        if($data['status']){     
            //Mapear orden de magento con IWS en tabla custom
            $this->saveIwsOrder($data['resp']->OrderNumber, $order->getId(), $order->getIncrementId());
            $this->addOrderComment($order->getId(), $data['resp']->OrderNumber);
            return true;
        } else {
            if(strpos((string)$configData['errores'], (string)$data['status_code']) !== false){
                if($configData['ordenes_reintentos']>$attempts){
                    $attempts++;
                    $this->logger->info('PlaceOrder - Error conexiÃ³n: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexiÃ³n. Se reintenta conexiÃ³n #'.$attempts.' con el servicio.');
                    sleep($configData['timeout']);
                    $this->beginPlaceOrder($configData, $payload, $serviceUrl, $order, $storeCode, $attempts);
                } else{
                    $this->logger->info('PlaceOrder - Error conexiÃ³n: '.$serviceUrl);
                    $this->logger->info('PlaceOrder - Se cumplieron el nÃºmero de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificaciÃ³n al correo '.$configData['ordenes_correo']);
                    $this->helper->notify('Soporte Trax', $configData['ordenes_correo'], $configData['ordenes_reintentos'], $serviceUrl, $payload, $storeCode);
                }
            }
           return false; 
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

    //Se guarda informaciÃ³n de IWS en tabla custom
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
	public function loadPayloadService($order, $storeCode, $configDataStoreId, $configDataImpuesto, $configItemImpuesto) 
	{        
        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        $orderItems = $order->getAllItems();
        $coupon = array();
        $configDataImpuesto = $configDataImpuesto/100;
        $shippingAmount = $order->getShippingAmount();
        if($shippingAmount!=0){
            $shippingAmount = $shippingAmount / (1 + $configDataImpuesto);
        }
        $freightAmount = $order->getBaseShippingDiscountAmount();
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
        $this->logger->info('PlaceOrder - freightAmount: '.$freightAmount);
        $this->logger->info('PlaceOrder - shippingAmount: '.$shippingAmount);
        $shippingData = $this->loadShippingInformation($order, $shipping->getCountryId(), $storeCode);
        if(!$shippingData['CarrierId']){
            $this->logger->info('PlaceOrder - No se ha obtenido carrier ID');
            return false;
        }
        $items = array();
        $skuItems = array();
        /** @var \Magento\Sales\Model\Order\Item $dataItem */
        foreach ($orderItems as $key => $dataItem) {
            $originalPrice = $dataItem->getOriginalPrice();
            $qty = (int)$dataItem->getQtyOrdered();
            $price = $dataItem->getPrice();
            $productId = $dataItem->getProductId();
            $id = $dataItem->getId();
            $sku = $dataItem->getProduct()->getSku();
            $discountAmount = $dataItem->getDiscountAmount();
            $appliedRuleIds = $dataItem->getAppliedRuleIds();
            if ($dataItem->getParentItem() && $this->configurableProduct->getIsModuleEnabled($order->getStoreId())) {
                $originalPrice = $dataItem->getParentItem()->getOriginalPrice();
                $qty = (int)$dataItem->getParentItem()->getQtyOrdered();
                $price = $dataItem->getParentItem()->getPrice();
                $productId = $dataItem->getParentItem()->getProductId();
                $id = $dataItem->getParentItem()->getItemId();
                $sku = explode($this->configurableProduct->getSeparator($order->getStoreId()), $dataItem->getSku())[1];
                $discountAmount = $dataItem->getParentItem()->getDiscountAmount();
                $appliedRuleIds = $dataItem->getParentItem()->getAppliedRuleIds();
                $this->logger->info("ParentIsConfigurable SkuToSend: " . $sku);
            }
            if (!array_key_exists($dataItem->getSku(), $skuItems) && $originalPrice != 0 && $dataItem->getProduct()->getTypeId() !== Configurable::TYPE_CODE) {
                $this->logger->info('SkuFinal: ' . $sku);
                $tempItem['Sku'] = $sku;
                $skuItems[$dataItem->getSku()] = $originalPrice;
                $tempItem['Quantity'] = $qty;
                $tempItem['Price'] = $originalPrice;
                $discount = '';
                if(count($coupon) == 0){
                    $price = $originalPrice - $price;
                    if($price > 0){
                        $discount = $price;
                    }
                }

                $coupon_prod = $coupon;
                $specialPrice = $this->getDataProductInfo($productId,$storeCode);

                if($this->credoHelper->isBinRule($appliedRuleIds)){
                    if($specialPrice > 0 ){
                        $discount = $originalPrice - $specialPrice;
                        $discount += $discountAmount;
                        $coupon_prod = '';
                    }else{
                        $discount = $discountAmount;
                    }
                }else{
                    if($specialPrice > 0 ){
                        $discount = $originalPrice - $specialPrice;
                        $coupon_prod = '';
                    }
                }

                $tempItem['Discounts'] = $discount;
                $tempItem['CouponCodes'] = $coupon_prod;
                $tempItem['StoreItemId'] = $id;
                $items[] = $tempItem;
            }
        }

        if (!empty($billing->getRfc())) {
            $identifi = $billing->getRfc();
        }else {
            $identifi = $this->getIdentification($billing,$shipping);
        }

        $discount = abs($order->getGiftCardsAmount()) + abs($order->getBaseDiscountAmount());
        $payload = array(
            'StoreOrder' => array(
                'StoreId' => $configDataStoreId,
                'StoreOrderNumber' => $order->getIncrementId(),
                'Customer' => array(
                    'FirstName' => $billing->getFirstname(),
                    'LastName' => $billing->getLastname(),
                    'Email' => $billing->getEmail(),
                    'Cellphone' => $billing->getTelephone(),
                    'DocumentId' => $billing->getIdentification()
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
                    'PostalCode' => $billing->getPostCode(),
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
                    'PostalCode' => $shipping->getPostCode(),
                ),
                'DeliveryType' => $order->getShippingMethod(),
            ),
            'Discounts' => $discount,
            'CouponCodes' => $coupon,
            'TaxRegistrationNumber' => $identifi,
            'TaxSystem' =>  $this->helper->clearSpecialCharac($this->getValueBillingAddress($billing->getRegimenFiscal(), 'regimen_fiscal')),
            'DigitalTaxReceipt' => $this->helper->clearSpecialCharac($this->getValueBillingAddress($billing->getCfdi(), 'cfdi')),
            'InvoiceRequested' => false,
            'ReceiveInvoiceByMail' => false,
            'Shipments' => array(
                array(
                    'FreightService' => "MiEnvio.mx",
                    'FreightShipmentId' => (!empty($order->getMienvioQuoteId()))?$order->getMienvioQuoteId() : 'N/A',
                    'ServiceType' => $shippingData['ServiceType'],
                    'CarrierId' => $shippingData['CarrierId'],
                    'Amount' => $shippingAmount,
                    'FreightCost' => $freightAmount,
                )
            ),
            'Items' => $items,
            'TaxesIncludedInPrice' => $configItemImpuesto
        );
        return json_encode($payload);
    }

    //Se Carga informaciÃ³n de carrier
    public function loadShippingInformation($order, $country, $storeCode) 
    {
        $orderShipping = explode(" - ", $order->getShippingDescription());
        $shipping['ServiceType'] = $orderShipping[1];
        $this->logger->info('PlaceOrder - ServiceType '.$orderShipping[1]);
        $shipping['CarrierId'] = $this->loadCarrierId($country, $orderShipping, $storeCode);     
        return $shipping;
	}

    //Se carga relaciÃ³n de carrier con trax
    public function loadCarrierId($country, $orderShipping, $storeCode)
    {   
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('trax_match_carrier'); 
        $this->logger->info('PlaceOrder - tableName '.$tableName);
        $this->logger->info('PlaceOrder - carrier '.$orderShipping[0]);
        $this->logger->info('PlaceOrder - country_code '.$country);
        $this->logger->info('PlaceOrder - storeCode '.$storeCode);
		//Select Data from table
        $sql = "Select * FROM " . $tableName." where carrier='".$orderShipping[0]."' AND country_code='".$country."' AND store_code='".$storeCode."'";
        $this->logger->info('PlaceOrder - SQL Carrier '. $sql);
        $trax = $connection->fetchAll($sql); 
        foreach ($trax as $key => $data) {
            return $data['trax_code'];
        }
        return false;
    }

    //Se aÃ±ade comentario interno a orden
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

   /*
    * When the billing and shipping address are different, the identification must be sent to IWS as null
    * @author Germán Cárdenas
    * @param \Magento\Sales\Model\Order\Address $billing  Object with the data of the billing address
    * @param \Magento\Sales\Model\Order\Address $shipping Object with the data of the shipping address 
    * @return string Customer identification
    */
    public function getIdentification($billing,$shipping)
    {
        if(strcmp($billing->getCountryId(),'GT')==0){
            if( strcmp($billing->getIdentification(),$shipping->getIdentification()) == 0){                
                return null;
            }
        }
        return $billing->getIdentification();    
    }

    /**
     * Get informtion special price of product by store
     * @return $_product
     */
    public function getDataProductInfo($product_id, $store_code)
    {

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
        // get array of stores with storecode as key
        $stores = $storeManager->getStores(true, true);
        // check stores array for this storecode
        $store_id = 0;
        if(isset($stores[$store_code])){
            $store_id = $stores[$store_code]->getId();
        }

        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        $product        = $productFactory->create();

        $productResourceModel = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product');
        $productResourceModel->load($product, $product_id);

        $product->setStoreId($store_id);

        $specialPrice = $product->getPriceInfo()->getPrice('special_price')->getValue();

        return $specialPrice;
    }

    public function getValueBillingAddress($billing, $field)
    {

        $attribute = $this->eavAttributeRepository->get('customer_address', $field);
        $optionText = $attribute->getSource()->getOptionText($billing);
        $arrayOptions = explode("-", $optionText);

        if (empty($arrayOptions[0])) {
            $valueText = '';
        } else {
            $valueText = $arrayOptions[0];
        }

        return $valueText;
    }
}
