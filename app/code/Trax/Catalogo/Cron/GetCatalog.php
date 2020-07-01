<?php
namespace Trax\Catalogo\Cron;
use \Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;

class GetCatalog {

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

    const PRODUCT_MPN = 'trax_catalogo/catalogo_iws/product_mpn';
    
    private $helper;
	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    protected $logger;

    protected  $productRepository;   
    
    /**
    * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory
    */
    protected $_resourceFactory;

    /**
     * DB connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    public function __construct(LoggerInterface $logger, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,     \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool, \Magento\Indexer\Model\IndexerFactory $indexerFactory,     \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory, \Trax\Catalogo\Helper\Email $email,
    \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
    ResourceConnection $resource
    ) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/getCatalog.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        //$this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
        $this->helper = $email;
        $this->_resourceFactory = $resourceFactory;
        $this->_connection = $resource->getConnection();
        
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
        
        //Se obtienen todos los websites 
        $websites = $storeManager->getWebsites();
        $storeArray = array();
        foreach ($websites as $key => $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    //Se obtienen parametros de configuración por Store
                    $configData = $this->getConfigParams($storeScope, $store->getCode());    
                    //Se carga el servicio por curl
                    if($configData['datos_iws']){  
                        $this->logger->info('GetCatalog - El website '.$website->getCode().' con store '.$website->getCode().' tiene habilitada la conexión con IWS para obtener el catalogo.');
                        $serviceUrl = $this->getServiceUrl($configData, 1, $store->getCode());
                        if($serviceUrl && !array_key_exists($store->getId(), $storeArray)){ 
                            $this->beginCatalogLoad($configData, $store, $serviceUrl, $website, 0); 
                            $storeArray[$store->getId()] = $store->getId();
                            //Se reindexa                            
                            //$this->reindexData();
                            //Se limpia cache
                            $this->cleanCache();
                        } else {
                            $this->logger->info('GetCatalog - No se genero url del servicio en el website: '.$website->getCode().' con store '.$store->getCode());
                        }     
                    } else {
                        $this->logger->info('GetCatalog - El website '.$website->getCode().' con store '.$website->getCode().' no tiene habilitada la conexión con IWS para obtener el catalogo con información general de los productos');
                        $this->loadCatalogSales($configData, $website->getCode(), $website->getDefaultStore(), $website->getDefaultStoreId());
                    }
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
        $configData['product_mpn'] = $this->scopeConfig->getValue(self::PRODUCT_MPN, $storeScope, $websiteCode);
        return $configData;
    }

    /*Genera la url de consumo del servicio
    * Si $type = 1 se obtiene la información general del catalogo
    * Si $type != 1 se obtiene el precio e inventario del catalogo
    */
	public function getServiceUrl($configData, $type, $storeCode) 
	{
        $storeCode = explode("_", $storeCode);
        if($type == 1){
            $url = 'getcatalog';
        } else {
            $url = 'getcatalogsalesdata';
        }
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
            $serviceUrl = $configData['url'].$url.'?locale='.$locale.'&apiKey='.$configData['apikey'].'&utcTimeStamp='.$utcTime.'&signature='.$signature.'&includePriceData=true&ncludeInventoriyData=true'; 
        }
        return $serviceUrl;
    }

    //Función recursiva para intentos de conexión
    public function beginCatalogLoad($configData, $store, $serviceUrl, $website, $attempts) 
    {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl);
        $this->logger->info('Response:');
        $this->logger->info($data);
        if($data['status']){
            $this->loadCatalogData($data['resp'], $website->getCode(), $store, $store->getId(), $configData, $website->getId());
        } else {
            if(strpos((string)$configData['errores'], (string)$data['status_code']) !== false){
                if($configData['catalogo_reintentos']>$attempts){
                    $attempts++;
                    $this->logger->info('GetCatalog - Error conexión: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexión');
                    sleep($configData['timeout']);
                    $this->logger->info('GetCatalog - Se reintenta conexión #'.$attempts.' con el servicio.');
                    $this->beginCatalogLoad($configData, $store, $serviceUrl, $website, $attempts);
                } else{
                    $this->logger->info('GetCatalog - Error conexión: '.$serviceUrl);
                    $this->logger->info('GetCatalog - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['catalogo_correo']);
                    $this->helper->notify('Soporte Trax', $configData['catalogo_correo'], $configData['catalogo_reintentos'], $serviceUrl, 'N/A', $store->getId());
                }
            }
        }   

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
        $this->logger->info('GetCatalog - '.$serviceUrl);
        $this->logger->info('GetCatalog - status code: '.$status_code);
        $this->logger->info('GetCatalog - curl errors: '.$curl_errors);
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

    //Reindexa los productos despues de consultar el catalogo de un store view
	public function reindexData() 
	{
        $indexerCollection = $this->_indexerCollectionFactory->create();
        $ids = $indexerCollection->getAllIds();
        foreach ($ids as $id) {
            $idx = $this->_indexerFactory->create()->load($id);
            $idx->reindexAll($id);
        } // this reindexes all
        $this->logger->info('GetCatalog - Se reindexa');
    }

    //Limpia cache despues de consultar el catalogo de un store view
	public function cleanCache() 
	{
        $types = array('config','collections','eav','full_page','translate');
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
        $this->logger->info('GetCatalog - Se limpia cache');
    }

    //Se genera metodo para consultar servicio de get catalog sales data
    public function loadCatalogSales($configData, $websiteCode, $store, $storeId) 
    {
        if($configData['datos_sales_iws']){
            $serviceUrl = $this->getServiceUrl($configData, 2, $store->getCode());
            if($serviceUrl){
                $this->beginCatalogSalesLoad($configData, $websiteCode, $store, $serviceUrl, $storeId, 0);
            } else {
                $this->logger->info('GetCatalogSalesData - El website '.$websiteCode.' con store '.$storeId.' no tiene habilitada la conexión con IWS');
            }
        } else {
            $this->logger->info('GetCatalogSalesData - El website '.$websiteCode.' con store '.$storeId.' no tiene habilitada la conexión con IWS para obtener precios e inventario de los productos');
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
                    $this->logger->info('GetCatalogSalesData - Error conexión: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexión');
                    sleep($configData['timeout']);
                    $this->logger->info('GetCatalogSalesData - Se reintenta conexión #'.$attempts.' con el servicio.');
                    $this->beginCatalogSalesLoad($configData, $websiteCode, $store, $serviceUrl, $storeId, $attempts);
                } else{
                    $this->logger->info('GetCatalogSalesData - Error conexión: '.$serviceUrl);
                    $this->logger->info('GetCatalogSalesData - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['catalogo_correo']);
                    $this->helper->notify('Soporte Trax', $configData['catalogo_correo'], $configData['catalogo_reintentos'], $serviceUrl, 'N/A', $store->getId());
                }
            }
        } 
    }

    //Carga la información del catalogo
    public function loadCatalogData($data, $websiteCode, $store, $storeId, $configData, $websiteId) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        //Se recorre array        
        $allCategories = array();
        $allProducts = array();
        //Se carga la categoria root del store
        $rootNodeId = $store->getRootCategoryId();
        /// Get Root Category
        $rootCat = $objectManager->get('Magento\Catalog\Model\Category');
        $cat_info = $rootCat->load($rootNodeId);

        foreach ($data as $key => $catalog) {

            $catId = $catalog->Category->CategoryId;

            if($catId == "" || $catId == null){
                $catId = "Def".$websiteCode;
            }

            $this->logger->info('GetCatalog - lee datos '.$websiteCode);
            $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            $categories = $categoryCollection->create()->addAttributeToFilter('iws_id',$catId)->addAttributeToFilter('path',array('like' => $rootCat->getPath().'%'));
            //Se valida si la categoría existe
            $arrayCategories = array();
            $existe = 0;
            
            if($categories->getSize()){
                $collection = $objectManager->create('Magento\Catalog\Model\Category');
                $categoryTmp = $collection->load($categories->getFirstItem()->getId(), $storeId);
                $existe = 1;
            } else {
                $categoryFactory=$objectManager->get('\Magento\Catalog\Model\CategoryFactory');
                /// Add a new sub category under root category
                $categoryTmp = $categoryFactory->create();
                $categoryTmp->setIsActive(false);
            }
            //Se asocian campos
            $name=ucfirst($catalog->Category->Description);
            if($name == "" || $name == null){
                $name = "default";
            }
            $url=strtolower($name.'-'.$catId.'-'.$rootNodeId.'-'.$storeId.'-'.$key.'-'.rand(0,1000));        
            $cleanurl = html_entity_decode(strip_tags($url));
            $categoryTmp->setUrlKey($cleanurl);            
            $categoryTmp->setName($name);
            $categoryTmp->setIncludeInMenu(true);
            $categoryTmp->setData('description', $catalog->Category->Description);
            if($existe == 0){ 
                $categoryCollection1 = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
                $categoriesAll = $categoryCollection1->create()->addAttributeToFilter('iws_id','all_categories')->addAttributeToFilter('parent_id',array('eq' => $rootNodeId));
                if($categoriesAll->getSize()){
                    foreach ($categoriesAll as $key => $data) {     
                        //Se asocia categoria
                        if($data->getParentId()==$rootCat->getId()){
                            $categoryTmp->setPath($data->getPath());  
                            $categoryTmp->setParentId($data->getId());
                            $this->logger->info('GetCatalog - Asigna parent: '.$data->getPath());
                            break;
                        }
                    }
                } else {
                    $categoryTmp->setPath($rootCat->getPath());    
                    $categoryTmp->setParentId($rootCat->getId());        
                    $this->logger->info('GetCatalog - No asigna parent: '.$rootCat->getId());
                }
            }
            if($catalog->Category->Subcategories && count($catalog->Category->Subcategories)>0){
                $categoryTmp->setIsAnchor(0);
                $categoryTmp->setPageLayout('1column');
            }
            //Corrige error de layout
            if($categoryTmp->getCustomLayoutUpdate() == '1column'){
                $categoryTmp->setCustomLayoutUpdate('');
            }

            $categoryTmp->setIwsId($catId);
            $categoryTmp->setStoreId($storeId);
            try{
                $categoryTmp->save();            
                $this->logger->info('GetCatalog - Guarda categoria: '.$categoryTmp->getId());
                $this->logger->info('GetCatalog - Categoria Padre: '.$categoryTmp->getParentId());
                $this->logger->info('GetCatalog - Categoria Path Padre: '.$categoryTmp->getPath());
                $arrayCategories[$categoryTmp->getId()] = $categoryTmp->getId();
                //Se valida si tiene subcategorias
                if($catalog->Category->Subcategories && count($catalog->Category->Subcategories)>0){
                    $arrayCategories = $this->loadSubcategoriesData($catalog->Category->Subcategories, $websiteCode, $store, $storeId, $categoryTmp->getId(), $arrayCategories);
                    $rootCat->load($rootNodeId);
                }
                //Se valida producto y se asocia a categoria
                $product_id = $this->loadProductsData($catalog, $objectManager, $storeId, $websiteId, $arrayCategories, $configData);
                //Se asocian categorias a productos
                if($product_id){
                    $allProducts[$product_id] = $product_id;
                }
                array_push($allCategories, $arrayCategories); 
            } catch (Exception $e){
                $this->logger->info('GetCatalog - Se ha producido un error al guardar la categoria '.$categoryTmp->getId().'. Error: '.$e->getMessage());
            }
        }         
        $this->loadCatalogSales($configData, $websiteCode, $store, $storeId);
        //Se verifican categorias no retornadas en el servicio y se deshabilitan
        $newArrayCategory = array ();
        foreach ($allCategories as $key => $categoryData) {
            foreach ($categoryData as $key1 => $category) {
                $newArrayCategory[$key1] = $category;
            }
        }
        $this->checkCategories($newArrayCategory, $store->getRootCategoryId(), $storeId);
        //Se verifican productos no retornados en el servicio y se deshabilitan
        $this->checkProducts($allProducts, $store->getRootCategoryId(), $storeId, $newArrayCategory);
    }

    //Carga la información de precios
    public function loadCatalogSalesData($data, $websiteCode, $store, $storeId, $configData) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $attributes     = array();     
        //Se recorre array
        foreach ($data as $key => $catalog) {
            $this->logger->info('GetCatalogSalesData - Lee datos. Website: '.$websiteCode);
            $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
            $products = $productFactory->create();
            //Se carga producto por SKU
            $product = $products->setStoreId($storeId)->loadByAttribute('sku', $catalog->Sku);            
            if(!$product || $product->getStatus()!=1){
                $this->logger->info('GetCatalogSalesData - Se ha producido un error al actualizar los datos del producto con SKU '.$catalog->Sku.' en el Website: '.$websiteCode.'. El producto no existe');
            } else {
                
                if($configData['product_price']){
                    $attributes['Price'] = $catalog->Price->UnitPrice;
                }

                try{
                    $this->setStoreViewDataProduct($product->getId(),$catalog->Sku,$storeId,$attributes);
                    $this->logger->info('GetCatalogSalesData - Se actualizan datos del producto con SKU '.$catalog->Sku.' en el Website: '.$websiteCode);
                } catch(Exception $e){
                    $this->logger->info('GetCatalogSalesData - Se ha producido un error al actualizar los datos del producto con SKU '.$catalog->Sku.' en el Website: '.$websiteCode.'. Error: '.$e->getMessage());
                }
            }
        } 
    }

    //Carga la información de las subcategorias
    public function loadSubcategoriesData($data, $websiteCode, $store, $storeId, $rootNodeId, $arrayCategories) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
        $appState = $objectManager->get('\Magento\Framework\App\State');
        //Se recorre array
        foreach ($data as $key => $catalog) {
            $catId = $catalog->CategoryId;

            if($catId == "" || $catId == null){
                $catId = "SubDef".$websiteCode;
            }

            //Se carga la categoria por atributo
            $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            $categories = $categoryCollection->create()->addAttributeToFilter('iws_id',$catId)->addAttributeToFilter('parent_id',array('eq' => $rootNodeId));
            $existe = 0;
            //Se valida si la categoría existe
            if($categories->getSize()){
                $collection = $objectManager->create('Magento\Catalog\Model\Category');
                $categoryTmp = $collection->load($categories->getFirstItem()->getId(), $storeId);
                $existe = 1;
            } else {
                $categoryFactory=$objectManager->get('\Magento\Catalog\Model\CategoryFactory');
                /// Add a new sub category under root category
                $categoryTmp = $categoryFactory->create();
                $categoryTmp->setIsActive(false);
            }
            /// Get Root Category
            $rootCat = $objectManager->get('Magento\Catalog\Model\Category');
            $cat_info = $rootCat->load($rootNodeId);

            //Se asocian campos
            $name=ucfirst($catalog->Description);
            if($name == "" || $name == null){
                $name = "default";
            }
            $url=strtolower($name.'-'.$catId.'-'.$rootNodeId.'-'.$storeId.'-'.$key.rand(0,1000));
            $cleanurl = html_entity_decode(strip_tags($url));
            $categoryTmp->setName($name);
            $categoryTmp->setUrlKey($cleanurl);
            $categoryTmp->setData('description', $catalog->Description);
            if($existe==0){
                $categoryTmp->setParentId($rootCat->getId());
                $categoryTmp->setPath($rootCat->getPath());
            }
            $categoryTmp->setIwsId($catId);
            $categoryTmp->setStoreId($storeId);  
            try{
                $categoryTmp->save();
                $this->logger->info('GetCatalog - Guarda subcategoria: '.$categoryTmp->getId());
                $this->logger->info('GetCatalog - Categoria Padre: '.$categoryTmp->getParentId());
                $this->logger->info('GetCatalog - Categoria Path Padre: '.$categoryTmp->getPath());
                $arrayCategories[$categoryTmp->getId()] = $categoryTmp->getId();
            } catch (Exception $e){
                $this->logger->info('GetCatalog - Se ha producido un error al guardar la subcategoria '.$categoryTmp->getId().'. Error: '.$e->getMessage());
            }
        }     
        return $arrayCategories;
    }

    //Carga la información de los productos
    public function loadProductsData($catalog, $objectManager, $storeId, $websiteId, $categoryIds, $configData) 
    {        
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        $products = $productFactory->create();
        $product = $products->setStoreId($storeId)->loadByAttribute('sku', $catalog->Sku);     

        if(!$product){
            $product = $objectManager->create('\Magento\Catalog\Model\Product');
            $product->setStoreId($storeId)->setSku($catalog->Sku); // Set your sku here
            $product->setStatus(0); // Status on product enabled/ disabled 1/0           
            $product->setUrlKey(html_entity_decode(strip_tags(strtolower($catalog->Description.'-'.$catalog->Sku.'-'.$storeId.'-'.rand(0,1000)))));
            $iwsDescription = explode("- ", $catalog->Description);
            $name = $iwsDescription[0];
            $description = "";
            if(isset($iwsDescription[1])){
                $name .= $iwsDescription[1];
            }
            if(isset($iwsDescription[2])){
                $name .= $iwsDescription[2];
                for($i = 3; $i < count($iwsDescription); $i++){
                    $description .= $iwsDescription[$i];
                }
            }        
            $categoryIds = array_unique(
                array_merge(
                    $product->getCategoryIds(),
                    $categoryIds
                )
            );    
            $websiteIds = array_unique(
                array_merge(
                    $product->getWebsiteIds(),
                    array($websiteId)
                )
            );
            $product->setCategoryIds($categoryIds);        
            if($configData['product_name']){
                $product->setName($name); // Name of Product        
            }   
            if($configData['product_description']){
                $product->setDescription($description); // Description of Product      
            }
            $product->setAttributeSetId($configData['attribute_id']); // Attribute set id
            $product->setWebsiteIds($websiteIds);
            $this->logger->info('GetCatalog - Se asocia website a producto: '.$websiteId);
            $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
            $product->setTaxClassId($configData['tax_id']); // Tax class id
            $this->logger->info('GetCatalog - Atribute id: '.$configData['attribute_id']);
            $this->logger->info('GetCatalog - Tax id: '.$configData['tax_id']);
            if($configData['product_mpn']){
                $product->setData('mpn',$catalog->Mpn); // Add Mpn
                $product->setCustomAttribute('mpn',$catalog->Mpn); // add Mpn
            }
            switch($catalog->Type){
                case 'Physical':
                    $product->setTypeId('simple');
                    break;
                case 'License':
                    $product->setTypeId('virtual');
                    break;
                case 'Warranty':
                    $product->setTypeId('configurable');
                    break;
                case 'Downloadable':
                    $product->setTypeId('downloadable');
                    break;
            } // type of product (simple/virtual/downloadable/configurable)
            //Set product dimensions
            if(isset($catalog->Freight)){
                if(isset($catalog->Freight->Item)){
                    if($configData['product_weight']){
                        $product->setWeight($catalog->Freight->Item->Weight);    
                    }
                    if($configData['product_length']){
                        $product->setData('length',$catalog->Freight->Item->Length);
                        $product->setData('ts_dimensions_height',$catalog->Freight->Item->Height);
                        $product->setCustomAttribute('ts_dimensions_length',$catalog->Freight->Item->Length);   
                    }
                    if($configData['product_width']){
                        $product->setData('width',$catalog->Freight->Item->Width);
                        $product->setData('ts_dimensions_width',$catalog->Freight->Item->Width);   
                        $product->setCustomAttribute('ts_dimensions_width',$catalog->Freight->Item->Width);   
                    }
                    if($configData['product_height']){
                        $product->setData('height',$catalog->Freight->Item->Height);
                        $product->setData('ts_dimensions_height',$catalog->Freight->Item->Height);
                        $product->setCustomAttribute('ts_dimensions_height',$catalog->Freight->Item->Height);
                    }
                }
            }
            //Set Stock 
            if($configData['product_stock']){
                if($catalog->InStock == 0){
                    $stock = 0;
                } else {
                    $stock = 1;
                }
                $data = array(
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'is_in_stock' => $stock,
                    'min_sale_qty' => 1,
                    'qty' => $catalog->InStock
                );
                $product->setStockData($data);
            }      
            //Set Price
            if($configData['product_price']){
                $product->setPrice($catalog->Price->UnitPrice);
            }

            try{
                $product->save();
                $this->logger->info('GetCatalog - Se guarda producto '.$product->getSku().' en el store: '.$storeId);
                return $product->getSku();
            } catch (Exception $e){
                $this->logger->info('GetCatalog - Se ha producido un error al guardar el producto con sku '.$catalog->Sku.'. Error: '.$e->getMessage());
                return false;
            }
        }
        else{

            $websitesData[]=['product_id' => $product->getId(), 'website_id' => $websiteId];

            $this->_saveProductWebsites($websitesData);

            $attibutes = array();

            $iwsDescription = explode("- ", $catalog->Description);
            $name = $iwsDescription[0];
            $description = "";
            if(isset($iwsDescription[1])){
                $name .= $iwsDescription[1];
            }
            if(isset($iwsDescription[2])){
                $name .= $iwsDescription[2];
                for($i = 3; $i < count($iwsDescription); $i++){
                    $description .= $iwsDescription[$i];
                }
            }        
              
            if($configData['product_name']){
                $attibutes ['Name'] = $name; // Name of Product        
            }   

            if($configData['product_description']){
                $attibutes ['Description'] = $description; // Description of Product      
            }

            if($configData['product_price']){
                $attributes['Price'] = $catalog->Price->UnitPrice;
            }
            
            try{
                $this->setStoreViewDataProduct( $product->getId(),$catalog->Sku,$storeId,$attibutes);
                $this->logger->info('GetCatalog - Se actualiza producto '.$product->getSku().' en el store: '.$storeId);
                return $product->getSku();
            } catch (Exception $e){
                $this->logger->info('GetCatalog - Se ha producido un error al actualizar el producto con sku '.$catalog->Sku.'. Error: '.$e->getMessage());
                return false;
            }
        }
  
    }

    //Deshabilita las categorias no retornadas en el servicio
    public function checkCategories($allCategories, $rootNodeId, $storeId) 
    {   
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
        $appState = $objectManager->get('\Magento\Framework\App\State');        
        $rootCat = $objectManager->get('Magento\Catalog\Model\Category');
        $cat_info = $rootCat->load($rootNodeId);
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryFactory->create()                              
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('path',array('like' => $rootCat->getPath().'%'));
        
        foreach ($categories as $category){
            if((!array_key_exists($category->getId(), $allCategories) && $category->getIwsId()!= '' && $category->getIwsId()!= 'N/A' && $category->getIwsId()!= 'all_categories' && $category->getIsActive()) || ($category->getName() == '' || !$category->getName())){
                $categoryFactoryData=$objectManager->get('\Magento\Catalog\Model\CategoryFactory');
                $categoryTmp = $categoryFactoryData->create()->setStoreId($storeId)->load($category->getId());     
                //Se deshabilita categoría            
                $categoryTmp->setIsActive(false);
                try{
                    $categoryTmp->save();            
                    $this->logger->info('GetCatalog - Se deshabilita categoria '.$categoryTmp->getId());
                } catch (Exception $e){
                    $this->logger->info('GetCatalog - Se ha producido un error al deshabilitar la categoria '.$categoryTmp->getId().'. Error: '.$e->getMessage());
                }
            }
        }
    }

    //Deshabilita los productos
    public function checkProducts($allProducts, $rootNodeId, $storeId, $allCategories) 
    {   
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
        $appState = $objectManager->get('\Magento\Framework\App\State');
        $productFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $products = $productFactory->create()                              
            ->addAttributeToSelect('*')
            ->addStoreFilter($storeId);
        
        foreach ($products as $product){
            if(!array_key_exists($product->getSku(), $allProducts) && $product->getStatus() != 0){
                $productFactoryData = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
                $products = $productFactoryData->create();
                $productTmp = $products->setStoreId($storeId)->load($product->getId());                
                $productTmp->setStatus(0); // Status on product enabled/ disabled 1/0
                try{
                    $productTmp->save();            
                    $this->logger->info('GetCatalog - Se deshabilita producto '.$productTmp->getSku());
                } catch (Exception $e){
                    $this->logger->info('GetCatalog - Se ha producido un error al deshabilitar el producto '.$productTmp->getSku().'. Error: '.$e->getMessage());
                }
            }
        }
    }

    /**
    * Update of products by store
    * @param $productId unique product identifier
    * @param $sku product sku
    * @param $storeId store id
    * @param $attibutes list of fields and values ​​to be updated
    * @author GDCP <german.cardenas@intcomex.com>
    * @return boolean
    */
    public function setStoreViewDataProduct($productId,$sku,$storeId,$attibutes)
    {
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        $product        = $productFactory->create();

        $productResourceModel = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product');
        $productResourceModel->load($product, $productId);
               
        $product->setStoreId($storeId);
        
        foreach($attibutes as $name => $value){

            $method = 'set'.$name;
            $product->$method($value);
            $productResourceModel->saveAttribute($product, strtolower($name));
           
        }

        return true;
        
    }

    /**
     * Save product websites.
     *
     * @param array $websiteData
     * @author GDCP <german.cardenas@intcomex.com>
     * @return boolean
     */
    protected function _saveProductWebsites(array $websitesData)
    {
        static $tableName = null;

        if (!$tableName) {
            $tableName = $this->_resourceFactory->create()->getProductWebsiteTable();
        }

        if ($websitesData) {
            $this->_connection->insertOnDuplicate($tableName, $websitesData);            
        }

        return true;
    }
}