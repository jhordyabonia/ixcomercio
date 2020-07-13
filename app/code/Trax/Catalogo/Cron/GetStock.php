<?php
namespace Trax\Catalogo\Cron;
use \Psr\Log\LoggerInterface;

class GetStock {

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';

	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';

	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';

	const TIMEOUT = 'trax_general/catalogo_retailer/timeout';

	const ERRORES = 'trax_general/catalogo_retailer/errores';

    const DATOS_TRAX = 'trax_catalogo/catalogo_general/datos_iws';

    const DATOS_SALES_TRAX = 'trax_catalogo/catalogo_general/datos_sales_iws';

    const DATOS_IMAGES_TRAX = 'trax_catalogo/catalogo_general/datos_images_iws';

    const CATALOGO_REINTENTOS = 'trax_catalogo/catalogo_general/catalogo_reintentos';

    const CATALOGO_CORREO = 'trax_catalogo/catalogo_general/catalogo_correo';

    const TAX_ID = 'trax_catalogo/catalogo_general/tax_id';

    const ATTRIBUTE_ID = 'trax_catalogo/catalogo_general/attribute_id';

    const PRODUCT_NAME = 'trax_catalogo/catalogo_iws/product_name';

    const PRODUCT_DESCRIPTION = 'trax_catalogo/catalogo_iws/product_description';

    const PRODUCT_WEIGHT = 'trax_catalogo/catalogo_iws/product_weight';

    const PRODUCT_LENGTH = 'trax_catalogo/catalogo_iws/product_length';

    const PRODUCT_WIDTH = 'trax_catalogo/catalogo_iws/product_width';

    const PRODUCT_HEIGHT = 'trax_catalogo/catalogo_iws/product_height';

    const PRODUCT_PRICE = 'trax_catalogo/catalogo_iws/product_price';

    const PRODUCT_STOCK = 'trax_catalogo/catalogo_iws/product_stock';
    
    private $helper;

	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    protected $logger;

    protected  $productRepository;     

    public function __construct(LoggerInterface $logger, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,     \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool, \Magento\Indexer\Model\IndexerFactory $indexerFactory,     \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory, \Trax\Catalogo\Helper\Email $email, \Magento\Store\Api\StoreRepositoryInterface $storesRepository) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/getStock.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        //$this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
        $this->_storesRepository = $storesRepository;
        $this->_sources = array();
        $this->helper = $email;
    }

/**
   * Write to system.log
   *
   * @return void
   */

    public function execute() 
    {
        //Se declaran variables de la tierra
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $sourceRepository = $objectManager->get('\Magento\InventoryApi\Api\SourceRepositoryInterface');
        
        $sources = $sourceRepository->getList()->getItems();
        foreach ($sources as $source) {
            $this->_sources[] = $source->getSourceCode();
        }
        //Se obtienen todos los websites 
        $stores = $this->_storesRepository->getList();
        foreach ($stores as $store) {
            $websiteId = $storeManager->getStore($store->getId())->getWebsiteId();
            $website = $storeManager->getWebsite($websiteId);
            //Se obtienen parametros de configuración por Store
            $configData = $this->getConfigParams($storeScope, $store->getCode()); 
            //Se carga el servicio por curl
            $this->logger->info('GetStock - Se carga stock en el website '.$website->getCode().' con store '.$website->getCode());
            $this->loadCatalogSales($configData, $website->getCode(), $website->getDefaultGroup(), $website->getDefaultGroup()->getDefaultStoreId()); 
        }
        //Se reindexa                            
        $this->reindexCatalogData();

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
        $configData['datos_iws'] = $this->scopeConfig->getValue(self::DATOS_TRAX, $storeScope, $websiteCode);
        $configData['datos_sales_iws'] = $this->scopeConfig->getValue(self::DATOS_SALES_TRAX, $storeScope, $websiteCode);
        $configData['datos_images_iws'] = $this->scopeConfig->getValue(self::DATOS_IMAGES_TRAX, $storeScope, $websiteCode);
        $configData['catalogo_reintentos'] = $this->scopeConfig->getValue(self::CATALOGO_REINTENTOS, $storeScope, $websiteCode);
        $configData['catalogo_correo'] = $this->scopeConfig->getValue(self::CATALOGO_CORREO, $storeScope, $websiteCode);
        $configData['attribute_id'] = $this->scopeConfig->getValue(self::ATTRIBUTE_ID, $storeScope, $websiteCode);
        $configData['tax_id'] = $this->scopeConfig->getValue(self::TAX_ID, $storeScope, $websiteCode);
        $configData['product_name'] = $this->scopeConfig->getValue(self::PRODUCT_NAME, $storeScope, $websiteCode);
        $configData['product_description'] = $this->scopeConfig->getValue(self::PRODUCT_DESCRIPTION, $storeScope, $websiteCode);
        $configData['product_weight'] = $this->scopeConfig->getValue(self::PRODUCT_WEIGHT, $storeScope, $websiteCode);
        $configData['product_length'] = $this->scopeConfig->getValue(self::PRODUCT_LENGTH, $storeScope, $websiteCode);
        $configData['product_width'] = $this->scopeConfig->getValue(self::PRODUCT_WIDTH, $storeScope, $websiteCode);
        $configData['product_height'] = $this->scopeConfig->getValue(self::PRODUCT_HEIGHT, $storeScope, $websiteCode);
        $configData['product_price'] = $this->scopeConfig->getValue(self::PRODUCT_PRICE, $storeScope, $websiteCode);
        $configData['product_stock'] = $this->scopeConfig->getValue(self::PRODUCT_STOCK, $storeScope, $websiteCode);
        return $configData;
    }

    /*Genera la url de consumo del servicio
    * Si $type = 1 se obtiene la información general del catalogo
    * Si $type != 1 se obtiene el precio e inventario del catalogo
    */
	public function getServiceUrl($configData, $storeCode) 
	{
        $storeCode = explode("_", $storeCode);
        $url = 'getinventory';
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
            $serviceUrl = $configData['url'].$url.'?locale='.$locale.'&apiKey='.$configData['apikey'].'&utcTimeStamp='.$utcTime.'&signature='.$signature.'&includePriceData=false&includeInventoryData=false'; 
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
        $this->logger->info('GetStock - status code: '.$status_code);
        $this->logger->info('GetStock - '.$serviceUrl);
        $this->logger->info('GetStock - curl errors: '.$curl_errors);
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

    //Se genera metodo para consultar servicio de get catalog sales data
    public function loadCatalogSales($configData, $websiteCode, $store, $storeId) 
    {
        if($configData['datos_sales_iws']){
            $serviceUrl = $this->getServiceUrl($configData, $store->getCode());
            if($serviceUrl){
                $this->beginCatalogSalesLoad($configData, $websiteCode, $store, $serviceUrl, $storeId, 0);
            } else {
                $this->logger->info('GetStock - El website '.$websiteCode.' con store '.$storeId.' no tiene habilitada la conexión con IWS');
            }
        } else {
            $this->logger->info('GetStock - El website '.$websiteCode.' con store '.$storeId.' no tiene habilitada la conexión con IWS para obtener precios e inventario de los productos');
        }

    }

    //Función recursiva para intentos de conexión
    public function beginCatalogSalesLoad($configData, $websiteCode, $store, $serviceUrl, $storeId, $attempts) 
    {
        //Se conecta al servicio
        $data = $this->loadIwsService($serviceUrl);
        if($data['status']){              
            $this->loadCatalogSalesData($data['resp'], $websiteCode, $store, $storeId, $configData);
        } else {
            if(strpos((string)$configData['errores'], (string)$data['status_code']) !== false){
                if($configData['catalogo_reintentos']>$attempts){
                    $attempts++;
                    $this->logger->info('GetStock - Error conexión: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexión');
                    sleep($configData['timeout']);
                    $this->logger->info('GetStock - Se reintenta conexión #'.$attempts.' con el servicio.');
                    $this->beginCatalogSalesLoad($configData, $websiteCode, $store, $serviceUrl, $storeId, $attempts);
                } else{
                    $this->logger->info('GetStock - Error conexión: '.$serviceUrl);
                    $this->logger->info('GetStock - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['catalogo_correo']);
                    $this->helper->notify('Soporte Trax', $configData['catalogo_correo'], $configData['catalogo_reintentos'], $serviceUrl, 'N/A', $store->getId());
                }
            }
        } 
    }

    //Carga la información de precios e inventario del catalogo
    public function loadCatalogSalesData($data, $websiteCode, $store, $storeId, $configData) 
    {
        $arrSourceItemInterfaces = array();
        $arrayProducts = array();
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();  
        if(!in_array($websiteCode,$this->_sources)){
            $this->logger->info('The source inventory with code '.$websiteCode.' does not exist');
        }else{
            $this->logger->info('GetStock - Lee datos. Website: '.$websiteCode);
        
            $productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            $objSourceItemInterfaceFactory = $objectManager->get('Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory');
            $products = $productCollectionFactory->create();
            $products->addAttributeToSelect('*');
            $products->addStoreFilter($storeId);
            $productFix  = array();
            foreach($products->getData() as $key => $value){
                $productFix[$value['sku']] = $value;
            }
        
        
            if($configData['product_stock']){
                foreach ($data as $key => $catalog) {
                    try{
                    if(isset($productFix[$catalog->Sku])){
                        $objSourceItemInterface = $objSourceItemInterfaceFactory->create();
                        $objSourceItemInterface->setSku($catalog->Sku);
                        $objSourceItemInterface->setSourceCode($websiteCode);
                        $objSourceItemInterface->setQuantity($catalog->InStock);
                        $objSourceItemInterface->setStatus((($catalog->InStock > 0)?1:0));
                                                
                        $arrSourceItemInterfaces[] = $objSourceItemInterface; 
                        $arrayProducts[] = array(
                            'sku' => $catalog->Sku,
                            'websiteCode' => $websiteCode,
                            'InStock' => $catalog->InStock,
                            'Status' => (($catalog->InStock > 0)?1:0)
                        );
                    }
                    } catch(Exception $e){
                        $this->logger->info('GetStock - Se ha producido un error al actualizar los datos del producto con SKU '.$catalog->Sku.' en el Website: '.$websiteCode.'. Error: '.$e->getMessage());
                    }
                } 
            }

        }
        
        if(!empty($arrayProducts)){
            $this->_setStoreViewStock($arrSourceItemInterfaces,$arrayProducts);
        }
    }

    //Reindexa los productos despues de consultar el catalogo de un store view
	public function reindexCatalogData() 
	{
        $indexerCollection = $this->_indexerCollectionFactory->create();
        $id = "cataloginventory_stock";
        $idx = $this->_indexerFactory->create()->load($id);
        $idx->reindexAll($id); 
        $this->logger->info('GetStock - Se reindexa');
    }

    public function _setStoreViewStock($arrSourceItemInterfaces,$arrayProducts){

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();    
       
        $objSourceItemsSaveInterface   = $objectManager->get('Magento\InventoryApi\Api\SourceItemsSaveInterface');
                                
        try{
             $objSourceItemsSaveInterface->execute($arrSourceItemInterfaces);    
            $this->logger->info('GetStock - Se actualizan datos '.print_r($arrayProducts,true));
        }catch(Exception $e){
            $this->logger->info('GetStock - Se ha producido un error al actualizar los datos '.print_r($arrayProducts,true).' . Error: '.$e->getMessage());
        }
    }
}