<?php
namespace Intcomex\MienvioRewrites\Model\Carrier;
use Magento\Store\Model\ScopeInterface;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;
use MienvioMagento\MienvioGeneral\Helper\Data as Helper;
use Intcomex\MienvioRewrites\Helper\Data as Helperkit;



class Mienviorates extends AbstractCarrier implements CarrierInterface
{
    /**
     * Directory Helper
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;
    private $quoteRepository;

    const LEVEL_1_COUNTRIES = ['PE', 'CL','CO','GT'];
    const API_KEY = 'trax_general/catalogo_retailer/apikey';
    const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

    /**
     * Defines if quote endpoint will be used at rates
     * @var boolean
     */
    const IS_QUOTE_ENDPOINT_ACTIVE = true;

    protected $_storeManager;

    protected $_productRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        Helper $helperData,
        Helperkit $helperkit,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Cdi\Custom\Helper\Data $helperDataCdi,
        \Trax\Catalogo\Helper\Email $email,
        \Magento\Catalog\Model\Product $productRepository,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_code = 'mienviocarrier';
        $this->lbs_kg = 0.45359237;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_logger = $logger;
        $this->_curl = $curl;
        $this->_mienvioHelper = $helperData;
        $this->_kitHelper = $helperkit;
        $this->directoryHelper = $directoryHelper;
        $this->helperDataCdi = $helperDataCdi;
        $this->email = $email;
        $this->_productRepository = $productRepository;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Retrieve allowed methods
     *
     * @return string
     */
    public function getAllowedMethods()
    {
        return [
            $this->getCarrierCode() => __($this->getConfigData('name'))
        ];
    }

    /**
     * Checks if mienvio's configuration is ready
     *
     * @return boolean
     */
    private function checkIfMienvioEnvIsSet()
    {
        $isActive = $this->_mienvioHelper->isMienvioActive();
        $apiKey = $this->_mienvioHelper->getMienvioApi();
        $apiSource = $this->getConfigData('apikey');

        if (!$isActive) {
            return false;
        }

        if ($apiKey == "" || $apiSource == "NA") {
            return false;
        }
    }

    /**
     * Checks if mienvio's configuration is ready
     *
     * @return boolean
     */
    private function checkIfIsFreeShipping()
    {
        $isActive = $this->_mienvioHelper->isFreeShipping();
        if (!$isActive) {
            return false;
        }else{
            return true;
        }
    }

    /**
     * Process full street string and retrieves street and suburb
     *
     * @param  string $fullStreet
     * @return array
     */
    private function processFullAddress($fullStreet)
    {
        $response = [
            'street' => '.',
            'suburb' => '.'
        ];

        if ($fullStreet != null && $fullStreet != "") {
            $fullStreetArray = explode("\n", $fullStreet);
            $count = count($fullStreetArray);

            if ($count > 0 && $fullStreetArray[0] !== false) {
                $response['street'] = $fullStreetArray[0];
            }

            if ($count > 1 && $fullStreetArray[1] !== false) {
                $response['suburb'] = $fullStreetArray[1];
            }
        }

        return $response;
    }

    /**
     * Retrieve rates for given shipping request
     *
     * @param  RateRequest $request
     * @return [type]               [description]
     */
    public function collectRates(RateRequest $request)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scpConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $shippingAddress = $cart->getQuote()->getShippingAddress();

        $freeShippingSet = $shippingAddress->getFreeShipping();

        
        $campoMienvio = $scpConfig->getValue('tradein/general/campo_mienvio',ScopeInterface::SCOPE_STORE);
        $valorCampoMienvio = $scpConfig->getValue('tradein/general/valor_campo_mienvio',ScopeInterface::SCOPE_STORE);

        $shippingAddress = $cart->getQuote()->getShippingAddress();
        $rateResponse = $this->_rateResultFactory->create();
        $apiKey = $this->_mienvioHelper->getMienvioApi();
        if($apiKey == null){
            $apiKey = $this->_mienvioHelper->getMienvioApiRedundant();
        }
        $baseUrl =  $this->_mienvioHelper->getEnvironment();
        $createShipmentUrl  = $baseUrl . 'api/shipments';
        $quoteShipmentUrl   = $baseUrl . 'api/shipments/$shipmentId/rates';
        $getPackagesUrl     = $baseUrl . 'api/packages';
        $createAddressUrl   = $baseUrl . 'api/addresses';
        $createQuoteUrl     = $baseUrl . 'api/quotes';

        $itemsMeasures = $this->checkVirtualProducts($request->getAllItems());
        if($itemsMeasures){
            return $rateResponse;
        }

        try {
            /* ADDRESS CREATION */
            $destCountryId  = $request->getDestCountryId();
            $destCountry    = $request->getDestCountry();
            $destRegion     = $request->getDestRegionId();
            $destRegionCode = $request->getDestRegionCode();
            $destFullStreet = $request->getDestStreet();
            $fullAddressProcessed = $this->processFullAddress($destFullStreet);
            $destCity       = $request->getDestCity();
            $destPostcode   = $request->getDestPostcode();
            $fromData = $this->createAddressDataStr('from',
                "MIENVIO DE MEXICO",
                $this->_mienvioHelper->getOriginStreet(),
                $this->_mienvioHelper->getOriginStreet2(),
                $this->_mienvioHelper->getOriginZipCode(),
                "ventas@mienvio.mx",
                "5551814040",
                '',
                $destCountryId,
                $this->_mienvioHelper->getOriginCity()
            );

            $toData = $this->createAddressDataStr('to',
                'usuario temporal',
                'calle temporal',
                $fullAddressProcessed['suburb'],
                $destPostcode,
                "ventas@mienvio.mx",
                "5551814040",
                $fullAddressProcessed['suburb'],
                $destCountryId,
                $destRegion,
                $destRegionCode,
                $destCity
            );


            $options = [ CURLOPT_HTTPHEADER => ['Content-Type: application/json', "Authorization: Bearer {$apiKey}"]];
            $this->_curl->setOptions($options);
            $this->_logger->debug('APIKEY', ['url' => $apiKey]);
            $this->_logger->debug('URL MIENVIO CREATE ADDRESS', ['url' => $createAddressUrl]);
            $this->_logger->debug('FROM DATA', ['url' => $fromData]);
            $this->_logger->debug('TO DATA', ['url' => $toData]);
            $this->_curl->post($createAddressUrl, json_encode($fromData));
            $addressFromResp = json_decode($this->_curl->getBody());
            $this->_logger->debug($this->_curl->getBody());
            $addressFromId = $addressFromResp->{'address'}->{'object_id'};

            $this->_curl->post($createAddressUrl, json_encode($toData));
            $addressToResp = json_decode($this->_curl->getBody());
            $this->_logger->debug($this->_curl->getBody());
            $addressToId = $addressToResp->{'address'}->{'object_id'};

            $itemsMeasures = $this->getOrderDefaultMeasures($request->getAllItems());
            $packageWeight = $this->convertWeight($request->getPackageWeight());

            if (self::IS_QUOTE_ENDPOINT_ACTIVE) {
                $rates = $this->quoteShipmentViaQuoteEndpoint(
                    $itemsMeasures['items'], $addressFromId, $addressToId, $createQuoteUrl
                );
            } else {
                $rates = $this->quoteShipment(
                    $itemsMeasures, $packageWeight, $getPackagesUrl,
                    $createShipmentUrl, $options, $packageValue, $fromZipCode);
            }

            $verifyTradeIn = $this->verifyTradeIn($request->getAllItems());

            foreach ($rates as $rate) {
                if($verifyTradeIn){
                  if(!isset($rate[$campoMienvio])){
                      continue;
                    }
                }
                $this->_logger->debug('rate_id');
                $methodId = $this->parseReverseServiceLevel($rate['servicelevel']) . '-' . $rate['courier'];
                $this->_logger->debug((string)$methodId);
                $this->_logger->debug(strval($rate['id']));
                

                $method = $this->_rateMethodFactory->create();
                $method->setCarrier($this->getCarrierCode());
                $method->setCarrierTitle($rate['courier']);
                $method->setMethod((string)$methodId);

                $method->setMethodTitle($rate['servicelevel'].' - '.$rate['duration_terms']);
                if($freeShippingSet){
                    $method->setPrice(0);
                    $method->setCost(0);
                }else{
                    $method->setPrice($rate['cost']);
                    $method->setCost($rate['cost']);
                }

                

                $rateResponse->append($method);
            }


        } catch (\Exception $e) {
            $this->_logger->debug("Rates Exception");
            $this->_logger->debug($e);
        }

        return $rateResponse;
    }

    /**
     * Quotes shipment using the quote endpoint
     *
     * @param  array $items
     * @param  integer $addressFromId
     * @param  integer $addressToId
     * @param  string $createQuoteUrl
     * @return string
     */
    private function quoteShipmentViaQuoteEndpoint($items, $addressFromId, $addressToId, $createQuoteUrl)
    {
        $quoteReqData = [
            'items'         => $items,
            'address_from'  => $addressFromId,
            'address_to'    => $addressToId,
            'shop_url'     => $this->_storeManager->getStore()->getUrl()
        ];

        $this->_logger->debug('Creating quote (mienviorates)', ['request' => json_encode($quoteReqData)]);
        $this->_logger->debug('URL MIENVIO', ['url' => $createQuoteUrl]);
        $this->_curl->post($createQuoteUrl, json_encode($quoteReqData));
        $quoteResponse = json_decode($this->_curl->getBody());
        $this->_logger->debug('Creating quote (mienviorates)', ['response' => $this->_curl->getBody()]);

        if (isset($quoteResponse->{'rates'})) {
            $rates = [];

            foreach ($quoteResponse->{'rates'} as $key => $rate) {
                if($rate->{'servicelevel'} == 'worlwide_usa' || $rate->{'servicelevel'} == 'worldwide_usa'){

                }else{
                    $rates[] = [
                        'courier'      => $rate->{'provider'},
                        'servicelevel' => $this->parseServiceLevel($rate->{'servicelevel'}),
                        'id'           => $quoteResponse->{'quote_id'},
                        'cost'         => $rate->{'amount'},
                        'key'          => $rate->{'provider'} . '-' . $rate->{'servicelevel'},
                        'duration_terms' => $rate->{'duration_terms'}
                    ];
                }



            }

            return $rates;
        }

        return [[
            'courier'      => $quoteResponse->{'courier'},
            'servicelevel' => $quoteResponse->{'servicelevel'},
            'id'           => $quoteResponse->{'quote_id'},
            'cost'         => $quoteResponse->{'cost'}
        ]];
    }

    private  function parseServiceLevel($serviceLevel){
        $parsed = '';
        switch ($serviceLevel) {
            case 'estandar':
                $parsed = 'Estándar';
                break;
            case 'express':
                $parsed = 'Express';
                break;
            case 'saver':
                $parsed = 'Saver';
                break;
            case 'express_plus':
                $parsed = 'Express Plus';
                break;
            case 'economy':
                $parsed = 'Economy';
                break;
            case 'priority':
                $parsed = 'Priority';
                break;
            case 'worlwide_usa':
                $parsed = 'World Wide USA';
                break;
            case 'worldwide_usa':
                $parsed = 'World Wide USA';
                break;
            case 'regular':
                $parsed = 'Regular';
                break;
            case 'regular_mx':
                $parsed = 'Regular MX';
                break;
            case 'BE_priority':
                $parsed = 'Priority';
                break;
            case 'flex':
                $parsed = 'Flex';
                break;
            case 'scheduled':
                $parsed = 'Programado';
                break;
            default:
                $parsed = $serviceLevel;
        }

        return $parsed;

    }


    private  function parseReverseServiceLevel($serviceLevel){
        $parsed = '';
        switch ($serviceLevel) {
            case 'Estándar' :
                $parsed = 'estandar';
                break;
            case 'Express' :
                $parsed = 'express';
                break;
            case 'Saver' :
                $parsed = 'saver';
                break;
            case 'Express Plus' :
                $parsed = 'express_plus';
                break;
            case 'Economy' :
                $parsed = 'economy';
                break;
            case 'Priority' :
                $parsed = 'priority';
                break;
            case 'World Wide USA' :
                $parsed = 'worlwide_usa';
                break;
            case 'World Wide USA' :
                $parsed = 'worldwide_usa';
                break;
            case 'Regular' :
                $parsed = 'regular';
                break;
            case 'Regular MX' :
                $parsed = 'regular_mx';
                break;
            case 'Priority' :
                $parsed = 'BE_priority';
                break;
            case 'Flex' :
                $parsed = 'flex';
                break;
            case 'Programado' :
                $parsed = 'scheduled';
                break;
            default:
                $parsed = $serviceLevel;
        }

        return $parsed;

    }

    /**
     * Quotes shipment using given data
     *
     * @param  array $itemsMeasures
     * @param  float $packageWeight
     * @param  string $getPackagesUrl
     * @param  string $createShipmentUrl
     * @param  array $options
     * @param  float $packageValue
     * @param  string $fromZipCode
     * @return array
     */
    private function quoteShipment(
        $itemsMeasures, $packageWeight, $getPackagesUrl,
        $createShipmentUrl, $options, $packageValue, $fromZipCode)
    {
        $packageVolWeight = $itemsMeasures['vol_weight'];
        $orderLength      = $itemsMeasures['length'];
        $orderWidth       = $itemsMeasures['width'];
        $orderHeight      = $itemsMeasures['height'];
        $orderDescription = $itemsMeasures['description'];
        $numberOfPackages = 1;

        $packageVolWeight = ceil($packageVolWeight);
        $orderWeight      = $packageVolWeight > $packageWeight ? $packageVolWeight : $packageWeight;
        $orderDescription = substr($orderDescription, 0, 30);

        try {
            $packages = $this->getAvailablePackages($getPackagesUrl, $options);
            $packageCalculus = $this->calculateNeededPackage($orderWeight, $packageVolWeight, $packages);
            $chosenPackage   = $packageCalculus['package'];
            $numberOfPackages = $packageCalculus['qty'];

            $orderLength = $chosenPackage->{'length'};
            $orderWidth  = $chosenPackage->{'width'};
            $orderHeight = $chosenPackage->{'height'};
        } catch (\Exception $e) {
            $this->_logger->debug('Error when getting needed package', ['e' => $e]);
        }

        $this->_logger->debug('Order info', [
            'packageWeight' => $packageWeight,
            'volWeight'     => $packageVolWeight,
            'maxWeight'     => $orderWeight,
            'package'       => $chosenPackage,
            'description'   => $orderDescription,
            'numberOfPackages' => $numberOfPackages
        ]);

        $shipmentReqData = [
            'object_purpose' => 'QUOTE',
            'address_from'   => $addressFromId,
            'address_to'     => $addressToId,
            'weight'         => $orderWeight,
            'declared_value' => $packageValue,
            'description'    => $orderDescription,
            'source_type'    => 'api',
            'length'         => $orderLength,
            'width'          => $orderWidth,
            'height'         => $orderHeight
        ];

        $this->_curl->setOptions($options);
        $this->_curl->post($createShipmentUrl, json_encode($shipmentReqData));
        $shipmentResponse = json_decode($this->_curl->getBody());

        $shipmentId = $shipmentResponse->{'shipment'}->{'object_id'};

        $quoteShipmentUrl = str_replace('$shipmentId' , $shipmentId, $quoteShipmentUrl);
        $this->_curl->get($quoteShipmentUrl);
        $ratesResponse = json_decode($this->_curl->getBody());
        $responseArr = [];

        foreach ($ratesResponse->{'results'} as $rate) {
            if (is_object($rate)) {
                $responseArr[] = [
                    'courier'      => $rate->{'provider'},
                    'servicelevel' => $rate->{'servicelevel'},
                    'id'           => $rate->{'object_id'},
                    'cost'         => $rate->{'amount'}
                ];
            }
        }

        return $responseArr;
    }

    private function createQuoteFromItems($createQuoteUrl, $items, $addressFromId, $addressToId)
    {
        $quoteReqData = [
            'items' => $items,
            'address_from' => $addressFromId,
            'address_to' => $addressToId
        ];

        $this->_curl->post($createQuoteUrl, json_encode($quoteReqData));
        $this->_logger->debug('URL MIENVIO', ['url' => $createQuoteUrl]);
        $quoteResponse = json_decode($this->_curl->getBody());

        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->getCarrierCode());
        $method->setCarrierTitle($quoteResponse->{'courier'});
        $method->setMethodTitle($quoteResponse->{'servicelevel'});
        $method->setMethod($quoteResponse->{'quote_id'});
        $method->setPrice($rate->{'cost'});
        $method->setCost($rate->{'cost'});
        $rateResponse->append($method);

        return $rateResponse;
    }

    /**
     * Creates an string with the address data
     *
     * @param  string $name
     * @param  string $street
     * @param  string $street2
     * @param  string $zipcode
     * @param  string $email
     * @param  string $phone
     * @param  string $reference
     * @param  string $countryCode
     * @return string
     */
    private function createAddressDataStr($type,$name, $street, $street2, $zipcode, $email, $phone, $reference = '.', $countryCode,$destRegion = null, $destRegionCode = null, $destCity = null)
    {

        if(empty($street2) || $street2 == "."  ){
            $street = "calle";
            $street2 = $destCity;
        }

        $data = [
            'object_type' => 'PURCHASE',
            'name' => $name,
            'street' => $street,
            'street2' => $street2,
            'email' => $email,
            'phone' => $phone,
            'reference' => '',
            'country' => $countryCode
        ];

        $location = $this->_mienvioHelper->getLocation();
        $this->_logger->debug('LOCATION: '.$location);
        $this->_logger->debug('Country: '.$countryCode);
        $this->_logger->debug('STREET2: '.$street2);
        $this->_logger->debug('DestRegion: '.$destRegion);
        $this->_logger->debug('DestRegionCode: '.$destRegionCode);
        $this->_logger->debug('DesCity: '.$destCity);

        if($location == 'street2' ){

            if ($countryCode === 'MX') {
                $data['zipcode'] = $zipcode;
            } elseif ($countryCode === 'PA' || $countryCode === 'CO'){
                if($type === 'from'){
                    $data['level_1'] = $street2;
                    $data['level_2'] = $destRegion;
                }
                if($type === 'to'){
                    if($destCity != ''){
                        $data['level_1'] = $destCity;
                        $data['level_2'] = $destRegionCode;
                    }elseif ($destCity != ''){
                        $data['level_1'] = $destCity;
                        $data['level_2'] = $destRegionCode;
                    }
                }

            } else {
                $data['level_1'] = $street2;
                $data['level_2'] = $destCity;
            }



        }else if($location == 'zipcode' ){
            if ($countryCode === 'MX') {
                $data['zipcode'] = $zipcode;
            } else {
                $data['level_1'] = $zipcode;
                $data['level_2'] = $destCity;
            }

        }else{
            if ($countryCode === 'MX') {
                $data['zipcode'] = $zipcode;
            } else {
                $data['level_1'] = $zipcode;
                $data['level_2'] = $destCity;
            }
        }
        $this->_logger->debug('DATA COMPLETE: '.serialize($data));
        return $data;
    }

    /**
     * Retrieves total measures of given items
     *
     * @param  Items $items
     * @return
     */
    private function getOrderDefaultMeasures($items)
    {
        $packageVolWeight = 0;
        $orderLength = 0;
        $orderWidth = 0;
        $orderHeight = 0;
        $orderDescription = '';
        $itemsArr = [];

        foreach ($items as $item) {
            $iws_type = "";
            $productName = $item->getName();
            $orderDescription .= $productName . ' ';
            $product = $this->getProductByName($productName);
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/LogerKits.log');
            $this->_loggerKit = new \Zend\Log\Logger();
            $this->_loggerKit->addWriter($writer);
            
            if(array_key_exists("iws_type",$product->getData())){
                $iws_type = $product->getData('iws_type');
                if(!empty($iws_type) && $iws_type == 'Kit'){
                    $this->_loggerKit->info('item kit');
                    $this->_loggerKit->info($item->getSku());
                    $serviceUrl = $this->getServiceUrl($item->getSku());
                    if(!empty($serviceUrl)&&isset($serviceUrl)){ 
                        $itemsKit = $this->beginProductLoad($serviceUrl, 0);
                        if(isset($itemsKit) && !empty($itemsKit)){
                            $this->_loggerKit->info('beginProductLoad');
                            foreach($itemsKit as $itemKit){
                                if($this->_mienvioHelper->getMeasures() === 1){
                                    $length = $product->getData('ts_dimensions_length');
                                    $width  = $product->getData('ts_dimensions_width');
                                    $height = $product->getData('ts_dimensions_height');
                                    $weight = $product->getData('weight');
                                }else{
                                    $length = $this->convertInchesToCms($itemKit->Freight->Item->Length);
                                    $width  = $this->convertInchesToCms($itemKit->Freight->Item->Width);
                                    $height = $this->convertInchesToCms($itemKit->Freight->Item->Height);
                                    $weight = $this->convertWeight($itemKit->Freight->Item->Weight);
                                }
                                $orderLength += $length;
                                $orderWidth  += $width;
                                $orderHeight += $height;

                                $volWeight = $this->calculateVolumetricWeight($length, $width, $height);
                                $packageVolWeight += $volWeight;
    
                                $itemsArr[] = [
                                    'id' => $itemKit->Sku,
                                    'name' => $itemKit->Description,
                                    'length' => $length,
                                    'width' => $width,
                                    'height' => $height,
                                    'weight' => $weight,
                                    'volWeight' => $volWeight,
                                    'qty' => $itemKit->Quantity,
                                    'declared_value' => $itemKit->Price,
                                ];
                            }
                        }
                        $this->_loggerKit->info(print_r($itemsKit,true));
                    }else {
                        $this->_logger->info('GetProduct - No se genero url del servicio');
                    }
                }else{
                    if($this->_mienvioHelper->getMeasures() === 1){
                        $length = $product->getData('ts_dimensions_length');
                        $width  = $product->getData('ts_dimensions_width');
                        $height = $product->getData('ts_dimensions_height');
                        $weight = $product->getData('weight');
        
                    }else{
                        $length = $this->convertInchesToCms($product->getData('ts_dimensions_length'));
                        $width  = $this->convertInchesToCms($product->getData('ts_dimensions_width'));
                        $height = $this->convertInchesToCms($product->getData('ts_dimensions_height'));
                        $weight = $this->convertWeight($product->getData('weight'));
                    }
                    $orderLength += $length;
                    $orderWidth  += $width;
                    $orderHeight += $height;
        
                    $volWeight = $this->calculateVolumetricWeight($length, $width, $height);
                    $packageVolWeight += $volWeight;
                    $itemsArr[] = [
                        'id' => $item->getId(),
                        'name' => $productName,
                        'length' => $length,
                        'width' => $width,
                        'height' => $height,
                        'weight' => $weight,
                        'volWeight' => $volWeight,
                        'qty' => $item->getQty(),
                        'declared_value' => $item->getprice(),
                    ];
                }
            }else{
                $dimensions = $this->getDimensionItems($product);

                if(is_array($dimensions)){
                    $length = $dimensions['length'];
                    $width  = $dimensions['width'];
                    $height = $dimensions['height'];
                    $weight = $dimensions['weight'];
                }else{
                    $length = 2;
                    $width  = 2;
                    $height = 2;
                    $weight = 1;
                }

                $orderLength += $length;
                $orderWidth  += $width;
                $orderHeight += $height;

                $volWeight = $this->calculateVolumetricWeight($length, $width, $height);
                $packageVolWeight += $volWeight;
                $itemsArr[] = [
                    'id' => $item->getId(),
                    'name' => $productName,
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'weight' => $weight,
                    'volWeight' => $volWeight,
                    'qty' => $item->getQty(),
                    'declared_value' => $item->getprice(),
                ];
            }
        }

        return [
            'vol_weight'  => $packageVolWeight,
            'length'      => $orderLength,
            'width'       => $orderWidth,
            'height'      => $orderHeight,
            'description' => $orderDescription,
            'items'       => $itemsArr
        ];
    }

    /**
     * Calculates volumetric weight of given measures
     *
     * @param  float $length
     * @param  float $width
     * @param  float $height
     * @return float
     */
    private function calculateVolumetricWeight($length, $width, $height)
    {
        $volumetricWeight = round(((1 * $length * $width * $height) / 5000), 4);

        return $volumetricWeight;
    }

    /**
     * Retrieve user packages
     *
     * @param  string $baseUrl
     * @return array
     */
    private function getAvailablePackages($url, $options)
    {
        $this->_curl->setOptions($options);
        $this->_curl->get($url);
        $response = json_decode($this->_curl->getBody());
        $packages = $response->{'results'};

        return $packages;
    }

    /**
     * Retrieves weight in KG
     *
     * @param  float $_weigth
     * @return float
     */
    private function convertWeight($_weigth)
    {
        $storeWeightUnit = $this->directoryHelper->getWeightUnit();
        $weight = 0;

        switch ($storeWeightUnit) {
            case 'lbs':
                $weight = $_weigth * $this->lbs_kg;
                break;
            case 'kgs':
                $weight = $_weigth;
                break;
        }

        return ceil($weight);
    }

    /**
     * Convert inches to cms
     *
     * @param  float $inches
     * @return float
     */
    private function convertInchesToCms($inches)
    {
        return $inches * 2.54;
    }

    /**
     * Calculates needed package size for order items
     *
     * @param  float $orderWeight
     * @param  float $orderVolWeight
     * @param  array $packages
     * @return array
     */
    private function calculateNeededPackage($orderWeight, $orderVolWeight, $packages)
    {
        $chosenPackVolWeight = 10000;
        $chosenPackage = null;
        $biggerPackage = null;
        $biggerPackageVolWeight = 0;
        $qty = 1;

        foreach ($packages as $package) {
            $packageVolWeight = $this->calculateVolumetricWeight(
                $package->{'length'}, $package->{'width'}, $package->{'height'}
            );

            if ($packageVolWeight > $biggerPackageVolWeight) {
                $biggerPackageVolWeight = $packageVolWeight;
                $biggerPackage = $package;
            }

            if ($packageVolWeight < $chosenPackVolWeight && $packageVolWeight >= $orderVolWeight) {
                $chosenPackVolWeight = $packageVolWeight;
                $chosenPackage = $package;
            }
        }

        if (is_null($chosenPackage)) {
            // then use bigger package
            $chosenPackage = $biggerPackage;
            $sizeRatio = $orderVolWeight/$biggerPackageVolWeight;
            $qty = ceil($sizeRatio);
        }

        return [
            'package' => $chosenPackage,
            'qty' => $qty
        ];
    }

    public function getServiceUrl($sku)
	{
        $apiKeyTrax = $this->helperDataCdi->getStoreConfig(self::API_KEY);
        $accessKeyTrax = $this->helperDataCdi->getStoreConfig(self::ACCESS_KEY);
        $locale = 'es';
		if($apiKeyTrax == ''){
            $serviceUrl = false;
        } else {
            $utcTime = gmdate("Y-m-d").'T'.gmdate("H:i:s").'Z';
            $signature = $apiKeyTrax.','.$accessKeyTrax.','.$utcTime;
            $signature = hash('sha256', $signature);
            $serviceUrl = $this->_kitHelper->getKitUrlService().'?locale='.$locale.'&sku='.$sku.'&apiKey='.$apiKeyTrax.'&utcTimeStamp='.$utcTime.'&signature='.$signature;
        }
        return $serviceUrl;
    }

    //Función recursiva para intentos de conexión
    public function beginProductLoad($serviceUrl, $attempts) 
    {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl);
        if($data['status']){
            return $data['resp']->Components;
        } else {
			if($this->_kitHelper->getKitRetries()>$attempts){
				$attempts++;
				$this->_loggerKit->info('GetProduct - Error conexión: '.$serviceUrl);
				sleep(30);
				$this->_loggerKit->info('GetProduct - Se reintenta conexión #'.$attempts.' con el servicio.');
				$this->beginProductLoad($serviceUrl, $attempts);
			} else{
				$this->_loggerKit->info('GetProduct - Error conexión: '.$serviceUrl);
				$this->_loggerKit->info('GetProduct - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$this->_kitHelper->getKitEmail());
				$this->email->notify('Soporte Trax', $this->_kitHelper->getKitEmail(), $this->_kitHelper->getKitRetries(), $serviceUrl, 'N/A', '');
			}
        }   

    }

    //Carga el servicio de IWS por Curl
    public function loadIwsService($serviceUrl) 
    {
        $this->_curl->get($serviceUrl);
        $this->_loggerKit->info('loadIwsService');
        $this->_loggerKit->info('GetProduct - '.$serviceUrl);
		$response = array(
			'status' => true,
			'resp' => json_decode($this->_curl->getBody())
		);
        return $response;
    }

    public function getProductByName($name)
	{
		return $this->_productRepository->loadByAttribute("name",$name);
	}

    private function getDimensionItems($product){
        $length = 0;
        $width = 0;
        $height = 0;
        $weight = 0;
        if($product->getData('ts_dimensions_length') != 0 && $product->getData('ts_dimensions_length') != null) {
            if ($this->_mienvioHelper->getMeasures() === 1) {
                $length = $product->getData('ts_dimensions_length');
                $width = $product->getData('ts_dimensions_width');
                $height = $product->getData('ts_dimensions_height');
                $weight = $product->getData('weight');


            } else {
                $length = $this->convertInchesToCms($product->getData('ts_dimensions_length'));
                $width = $this->convertInchesToCms($product->getData('ts_dimensions_width'));
                $height = $this->convertInchesToCms($product->getData('ts_dimensions_height'));
                $weight = $this->convertWeight($product->getData('weight'));
            }
        }else if($product->getAttribute('length') != 0 && $product->getAttribute('length') != null){
            if ($this->_mienvioHelper->getMeasures() === 1) {
                $length = $product->getAttribute('length');
                $width = $product->getAttribute('width');
                $height = $product->getAttribute('height');
                $weight = $product->getAttribute('weight');
            } else {
                $length = $this->convertInchesToCms($product->getAttribute('length'));
                $width = $this->convertInchesToCms($product->getAttribute('width'));
                $height = $this->convertInchesToCms($product->getAttribute('height'));
                $weight = $this->convertWeight($product->getAttribute('weight'));
            }
        }else if($product->getData('length') != 0 && $product->getData('length') != null){
            if ($this->_mienvioHelper->getMeasures() === 1) {
                $length = $product->getData('length');
                $width = $product->getData('width');
                $height = $product->getData('height');
                $weight = $product->getData('weight');


            } else {
                $length = $this->convertInchesToCms($product->getData('length'));
                $width = $this->convertInchesToCms($product->getData('width'));
                $height = $this->convertInchesToCms($product->getData('height'));
                $weight = $this->convertWeight($product->getData('weight'));
            }
        }else{
            $length = 0.5;
            $width = 0.5;
            $height = 0.5;
            $weight = 0.2;
            $this->_logger->debug('SHIPMENT WITH ITEM MEASURES IN 0, only for testing porpuses', ['ITEMSSSSS' => serialize($product->getData())]);


            try{
                $length = $product->getAttributeText('length');
                $width = $product->getAttributeText('width');
                $height = $product->getAttributeText('height');
                $this->_logger->debug('SHIPMENT PRODUCT ATTRIBUTE TEXT', ['ITEM' => serialize($product->getAttributeText('length'))]);

            } catch (\Exception $e) {
                $this->_logger->debug("Measures Exception");
                $this->_logger->debug($e);
            }
        }
        return array(
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'weight' => $weight
        );
    }
    private function checkVirtualProducts($items){
        try{
            foreach ($items as $item) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $productName = $item->getName();
                $product = $objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('name', $productName);

                if($product->getData('ts_dimensions_length') != 0 && $product->getData('ts_dimensions_length') != null) {
                    return false;
                }else if($product->getData('length') != 0 && $product->getData('length') != null){
                    return false;
                }else{
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->_logger->debug("Validate Virtual products Exception");
            $this->_logger->debug($e);
        }
        return true;
    }

    public function verifyTradeIn($items){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        foreach($items as $item){
            $productObj = $productRepository->get($item->getSku());
            if(strtoupper($productObj->getData('iws_type'))=='TRADEIN'){
                return true;
            }
        }
        return false;

    }
}
