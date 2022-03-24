<?php
namespace Intcomex\Auditoria\Cron;
use \Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;

class GetPriceList {

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const URL_PRICELIST = 'auditoria/general/url_pricelist';

    const ERRORES = 'trax_general/catalogo_retailer/errores';

    const CATALOGO_REINTENTOS = 'trax_catalogo/catalogo_general/catalogo_reintentos';

    const CORREO_ALERTA = 'auditoria/general/correos_alerta';

    private $helper;

	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    protected $logger;

    protected  $productRepository;   
    
    protected $resourceConnection;

    public function __construct(
        LoggerInterface $logger, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,   
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory, 
        \Intcomex\Auditoria\Helper\Email $email, 
        \Magento\Store\Api\StoreRepositoryInterface $storesRepository,
        \Magento\Eav\Model\Config $eavConfig,
         ResourceConnection $resourceConnection
         ) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/getPriceList.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        //$this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
        $this->_storesRepository = $storesRepository;
        $this->_sources = array();
        $this->helper = $email;
        $this->_eavConfig = $eavConfig;
        $this->resourceConnection = $resourceConnection;
    }

/**
   * Write to system.log
   *
   * @return void
   */

    public function execute() 
    {
        //$this->helper->notify('Soporte Whitelabel', 0);
        //die();
        $this->logger->info('Inicia Cron de Auditoria');
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        //Se obtienen todos los websites 
        $stores = $this->_storesRepository->getList();
        foreach ($stores as $store) {
            $websiteId = $storeManager->getStore($store->getId())->getWebsiteId();
            $website = $storeManager->getWebsite($websiteId);
            //Se obtienen parametros de configuración por Store
            $configData = $this->getConfigParams($storeScope, $store->getCode()); 
            //Se carga el servicio por curl
            $serviceUrl = $this->getServiceUrl($configData, $store->getCode());
            $this->beginPriceListLoad($configData, $website->getCode(), $store, $serviceUrl, $website->getDefaultGroup()->getDefaultStoreId(), 0);
        }

    }

    public function getConfigParams($storeScope, $websiteCode) 
    {

        $configData['apikey'] = $this->scopeConfig->getValue(self::API_KEY, $storeScope, $websiteCode);
        $configData['accesskey'] = $this->scopeConfig->getValue(self::ACCESS_KEY, $storeScope, $websiteCode);
        $configData['url'] = $this->scopeConfig->getValue(self::URL_PRICELIST, $storeScope, $websiteCode);
        $configData['catalogo_reintentos'] = $this->scopeConfig->getValue(self::CATALOGO_REINTENTOS, $storeScope, $websiteCode);
        $configData['errores'] = $this->scopeConfig->getValue(self::ERRORES, $storeScope, $websiteCode);
        $configData['correo_alerta'] = $this->scopeConfig->getValue(self::CORREO_ALERTA, $storeScope, $websiteCode);
        return $configData;
    }

	public function getServiceUrl($configData, $storeCode) 
	{
        $storeCode = explode("_", $storeCode);
        if($storeCode[count($storeCode)-1] == 'es'){
            $locale = 'es';
        } else {
            $locale = 'en';
        }
        if($configData['apikey'] == ''){
            $serviceUrl = false;
        } else {
            $utcTime = gmdate("Y-m-d").'T'.gmdate("H:i:s").'Z';
            $signature = $configData['apikey'].','.$configData['accesskey'].','.$utcTime;
            $signature = hash('sha256', $signature);
            $serviceUrl = $configData['url'].'?locale='.$locale.'&apiKey='.$configData['apikey'].'&utcTimeStamp='.$utcTime.'&signature='.$signature; 
        }
        return $serviceUrl;
    }

    //Carga el servicio de IWS por Curl
    public function loadIwsService($serviceUrl) 
    {
        
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl
        ));
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($curl);
        curl_close($curl);    
        $this->logger->info('GetSPriceList - status code: '.$status_code);
        $this->logger->info('GetSPriceList - '.$serviceUrl);
        if ($status_code == '200'){
            $response = array(
                'status' => true,
                'resp' => json_decode($resp)
            );
        } else {
            $this->logger->info('GetSPriceList - curl errors: '.$curl_errors);
            $response = array(
                'status' => false,
                'status_code' => $status_code
            );
        }
        return $response;

    }

    //Función recursiva para intentos de conexión
    public function beginPriceListLoad($configData, $websiteCode, $store, $serviceUrl, $storeId, $attempts) 
    {
        //Se conecta al servicio
        $data = $this->loadIwsService($serviceUrl);
        if($data['status']){              
            $this->loadData($data['resp'], $websiteCode, $store, $storeId, $configData);
        } else {
            $errors = explode(',',$configData['errores']);
            if(in_array($data['status_code'],$errors)){
                if($configData['catalogo_reintentos']>$attempts){
                    $attempts++;
                    $this->logger->info('GetSPriceList - Error conexión: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexión - '.date('Y-m-d H:i:s'));
                    sleep($configData['timeout']);
                    $this->logger->info('GetSPriceList - Se reintenta conexión #'.$attempts.' con el servicio. - '.date('Y-m-d H:i:s'));
                    $this->beginPriceListLoad($configData, $websiteCode, $store, $serviceUrl, $storeId, $attempts);
                } else{
                    $this->logger->info('GetSPriceList - Error conexión: '.$serviceUrl);
                    $this->logger->info('GetSPriceList - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['correo_alerta']);
                }
            }else{
                $this->logger->info('No se identifica el error de conexión');
                $this->logger->info(print_r($data,true));
                $this->logger->info('---');
            }
        } 
    }

    //Carga la información de precios e inventario del catalogo
    public function loadData($data, $websiteCode, $store, $storeId, $configData) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 

        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        $products = $productFactory->create();
        $errors = '';

        $productResourceModel = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product');


        if(!empty($data)){
            $this->logger->info('Se obtienen los productos de '.$websiteCode);
            $this->logger->info('---- Start  ---');
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $style = 'style="border:1px solid"';
            foreach($data as $key => $value){
                
                $productRep = $products->loadByAttribute('sku', trim($value->Sku));
                if($productRep){

                    $products->setStoreId($storeId);
    
                    $productResourceModel->load($products,$productRep->getId());
    
    
                    $products->setPrecioReferencia($value->Price->UnitPrice);
                    $productResourceModel->saveAttribute($products,'precio_referencia');
    
                    $products->setMoneda($value->Price->CurrencyId);
                    $productResourceModel->saveAttribute($products,'moneda');
                    $saved = true;
                    if($saved){
                         $productObj = $products->loadByAttribute('sku',trim($value->Sku));
                         $storeCurrency = $storeManager->getStore($store->getId())->getCurrentCurrency()->getCode();
                         if(trim(strtoupper($value->Price->CurrencyId))!=trim(strtoupper($storeCurrency))){
                            $errors .= '<tr>';
                            $errors .= '<td '.$style.' >'.$value->Sku.'</td>';
                            $errors .= '<td '.$style.' >'.$websiteCode.'</td>';
                            $errors .= '<td '.$style.' >'.$value->Price->CurrencyId.'</td>';
                            $errors .= '<td '.$style.' >Moneda</td>';
                            $errors .= '</tr>';
                         }
                         if($value->Price->UnitPrice<=0){
                            $errors .= '<tr>';
                            $errors .= '<td '.$style.' >'.$value->Sku.'</td>';
                            $errors .= '<td '.$style.' >'.$websiteCode.'</td>';
                            $errors .= '<td '.$style.' >'.$value->Price->UnitPrice.'</td>';
                            $errors .= '<td '.$style.' >Precio 0</td>';
                            $errors .= '</tr>';
                         }
                        $this->logger->info('Producto Actualizado: '.$value->Sku);
                        $this->logger->info('Precio anterior: '.$productObj->getPrice().' - Precio Nuevo:'.$value->Price->UnitPrice);
                        $this->logger->info('test: '.$productObj->getPrecioReferencia().' - test:'.$productObj->getMoneda());
                        $this->logger->info('Moneda anterior: '.$storeCurrency.' - moneda nueva:'.$value->Price->CurrencyId);
    
                    }
                }


            }
            if($errors!=''){
                $extraError = 'Algunos valores no corresponden a los establecidos en la página '.$websiteCode;
                $this->helper->notify('Soporte Whitelabel',$errors,$extraError, $storeId);
            }
            $this->logger->info('---- End  ---');
        }else{
            $this->logger->info('Datos vacios para: '.$websiteCode); 
        }
    }

}