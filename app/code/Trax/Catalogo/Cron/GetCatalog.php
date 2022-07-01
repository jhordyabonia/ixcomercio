<?php

namespace Trax\Catalogo\Cron;

use Magento\Catalog\Model\Product;use Magento\Framework\Exception\NoSuchEntityException;

class GetCatalog
{

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

    const PRODUCT_MPN = 'trax_catalogo/catalogo_iws/product_mpn';

    const TEXT_MAIL = 'trax_catalogo/catalogo_iws/text_email_product_iws';

    /**
     * @var \Intcomex\Crocs\Model\ConfigurableProduct
     */
    protected $configurableProduct;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;   

    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;   

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $indexerCollectionFactory;

    /**
     * DB connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var \Trax\Catalogo\Helper\Email
     */
    protected $email;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory
     */
    protected $_resourceFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $_storesRepository;

    /**
     * @var \Zend\Log\Logger
     */
    private $logger_price;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    private $productAction;

    /**
     * @var array
     */
    private $product_iws_not;

     /**
     * @var array
     */
    private $processedProductsInCrocsEvent;

    /**
     * Class construct.
     *
     * @param \Intcomex\Crocs\Model\ConfigurableProduct $configurableProduct
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Indexer\Model\IndexerFactory $indexerFactory
     * @param \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
     * @param \Trax\Catalogo\Helper\Email $email
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Api\StoreRepositoryInterface $storesRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $productAction
     * @author GDCP <german.cardenas@intcomex.com>
     */
    public function __construct(
        \Intcomex\Crocs\Model\ConfigurableProduct $configurableProduct,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,        
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        \Trax\Catalogo\Helper\Email $email,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Api\StoreRepositoryInterface $storesRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction
    ) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/getCatalog.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        //logs product price
        $writer_price = new \Zend\Log\Writer\Stream(BP . '/var/log/automatic_price_change.log');
        $this->logger_price = new \Zend\Log\Logger();
        $this->logger_price->addWriter($writer_price);

        $this->configurableProduct = $configurableProduct;
        $this->eventManager = $eventManager;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;        
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
        $this->help_email = $email;
        $this->_resourceFactory = $resourceFactory;
        $this->_connection = $resource->getConnection();        
        $this->_storesRepository = $storesRepository;
        $this->productAction = $productAction;
        $this->product_iws_not = array();
        $this->processedProductsInCrocsEvent = array();
    }

    /**
     * @return mixed|array
     */
    public function getProcessedProductsInCrocsEvent(){
        return $this->processedProductsInCrocsEvent;
    }

    /**
     * @param array processed
     */
    public function setProcessedProductsInCrocsEvent(array $processed){
        $this->processedProductsInCrocsEvent = $processed;
    }

    /**
     * Write to system.log.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(): bool
    {
        $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        $storeManager=$objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeScope=\Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        foreach($this->_storesRepository->getList() as $store){
            
                if ($store->isActive()) {
            
                    $websiteId=$storeManager->getStore($store->getId())->getWebsiteId();
                    $website=$storeManager->getWebsite($websiteId);
                    $configData=$this->getConfigParams($storeScope,$store->getCode());

                    if($configData['datos_iws']){

                        $this->logger->info('GetCatalog - El website ' . $website->getCode() . ' con store ' . $website->getCode() . ' tiene habilitada la conexión con IWS para obtener el catalogo.');
                        $serviceUrl=$this->getServiceUrl($configData,1,$store->getCode());
    
                        if($serviceUrl)
                            $this->beginCatalogLoad($configData,$store,$serviceUrl,$website,0);
                        else
                            $this->logger->info('GetCatalog - No se genero url del servicio en el website: ' . $website->getCode() . ' con store ' . $store->getCode());

                    }else{
                        $this->logger->info('GetCatalog - El website ' . $website->getCode() . ' con store ' . $website->getCode() . ' no tiene habilitada la conexión con IWS para obtener el catalogo con información general de los productos');
                        //$this->loadCatalogSales($configData,$website->getCode(),$website->getDefaultGroup(),$website->getDefaultGroup()->getDefaultStoreId());
                    }
    
                }

        }
        
        $this->logger->info('GetCatalog - Products not iws ' . count($this->product_iws_not));


        if(count($this->product_iws_not) > 0){
            $this->notifyProductNoIWS($this->product_iws_not);
        }else{
            $this->logger->info('GetCatalog - No hay productos nuevos en Magento');
        }

        $this->cleanCache();

        return true;
    }

    //Obtiene los parámetros de configuración desde el cms
    public function getConfigParams($storeScope, $websiteCode)
    {

        //Se obtienen parametros de configuración por Store
        $configData['apikey'] = $this->scopeConfig->getValue(self::API_KEY, $storeScope, $websiteCode);
        $configData['accesskey'] = $this->scopeConfig->getValue(self::ACCESS_KEY, $storeScope, $websiteCode);
        $enviroment = $this->scopeConfig->getValue(self::ENVIROMENT, $storeScope, $websiteCode);
        //Se valida entorno para obtener url del servicio
        if ($enviroment == '0') {
            $configData['url'] = $this->scopeConfig->getValue(self::URL_DESARROLLO, $storeScope, $websiteCode);
        } else {
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
        if ($type == 1) {
            $url = 'getcatalog';
        } else {
            $url = 'getcatalogsalesdata';
        }
        if ($storeCode[count($storeCode) - 1] == 'es') {
            $locale = 'es';
        } else {
            $locale = 'en';
        }
        if ($configData['apikey'] == '') {
            $serviceUrl = false;
        } else {
            $utcTime = gmdate("Y-m-d") . 'T' . gmdate("H:i:s") . 'Z';
            $signature = $configData['apikey'] . ',' . $configData['accesskey'] . ',' . $utcTime;
            $signature = hash('sha256', $signature);
            $serviceUrl = $configData['url'] . $url . '?locale=' . $locale . '&apiKey=' . $configData['apikey'] . '&utcTimeStamp=' . $utcTime . '&signature=' . $signature . '&includePriceData=true&ncludeInventoriyData=true';
        }
        return $serviceUrl;
    }

    //Función recursiva para intentos de conexión
    public function beginCatalogLoad($configData, $store, $serviceUrl, $website, $attempts)
    {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl);
        if ($data['status']) {
            $this->logger->info('Count: ' . count($data['resp']));
            // foreach ($data['resp'] as $item) { $this->logger->info('Sku: ' . $item->Sku . ' Mpn: ' .$item->Mpn); }exit;
            $this->loadCatalogData($data['resp'], $website->getCode(), $store, $store->getId(), $configData, $website->getId());
        } else {
            $errors = explode(',',$configData['errores']);
            if(in_array($data['status_code'],$errors)){ 
                if ($configData['catalogo_reintentos'] > $attempts) {
                    $attempts++;
                    $this->logger->info('GetCatalog - Error conexión: ' . $serviceUrl . ' Se esperan ' . $configData['timeout'] . ' segundos para reintento de conexión');
                    sleep($configData['timeout']);
                    $this->logger->info('GetCatalog - Se reintenta conexión #' . $attempts . ' con el servicio.');
                    $this->beginCatalogLoad($configData, $store, $serviceUrl, $website, $attempts);
                } else {
                    $this->logger->info('GetCatalog - Error conexión: ' . $serviceUrl);
                    $this->logger->info('GetCatalog - Se cumplieron el número de reintentos permitidos (' . $attempts . ') con el servicio: ' . $serviceUrl . ' se envia notificación al correo ' . $configData['catalogo_correo']);
                    $this->help_email->notify('Soporte Trax', $configData['catalogo_correo'], $configData['catalogo_reintentos'], $serviceUrl, 'N/A', $store->getId());
                }
            }else{
                $this->logger->info('No se identifica el error de conexión');
                $this->logger->info(print_r($data,true));
                $this->logger->info('---');
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
        $this->logger->info('GetCatalog - ' . $serviceUrl);
        $this->logger->info('GetCatalog - status code: ' . $status_code);
        $this->logger->info('GetCatalog - curl errors: ' . $curl_errors);
        if ($status_code == '200') {
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
        $types = array('config', 'collections', 'eav', 'full_page', 'translate');
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
        
        $serviceUrl = $this->getServiceUrl($configData, 2, $store->getCode());
        if ($serviceUrl) {
            $this->beginCatalogSalesLoad($configData, $websiteCode, $store, $serviceUrl, $storeId, 0);
        } else {
            $this->logger->info('GetCatalogSalesData - El website ' . $websiteCode . ' con store ' . $storeId . ' no tiene habilitada la conexión con IWS');
        }
        
    }

    //Función recursiva para intentos de conexión
    public function beginCatalogSalesLoad($configData, $websiteCode, $store, $serviceUrl, $storeId, $attempts)
    {
        //Se conecta al servicio
        $data = $this->loadIwsService($serviceUrl);
        if ($data['status']) {
            $this->loadCatalogSalesData($data['resp'], $websiteCode, $store, $storeId, $configData);
        } else {
            if (strpos((string) $configData['errores'], (string) $data['status_code']) !== false) {
                if ($configData['catalogo_reintentos'] > $attempts) {
                    $attempts++;
                    $this->logger->info('GetCatalogSalesData - Error conexión: ' . $serviceUrl . ' Se esperan ' . $configData['timeout'] . ' segundos para reintento de conexión');
                    sleep($configData['timeout']);
                    $this->logger->info('GetCatalogSalesData - Se reintenta conexión #' . $attempts . ' con el servicio.');
                    $this->beginCatalogSalesLoad($configData, $websiteCode, $store, $serviceUrl, $storeId, $attempts);
                } else {
                    $this->logger->info('GetCatalogSalesData - Error conexión: ' . $serviceUrl);
                    $this->logger->info('GetCatalogSalesData - Se cumplieron el número de reintentos permitidos (' . $attempts . ') con el servicio: ' . $serviceUrl . ' se envia notificación al correo ' . $configData['catalogo_correo']);
                    $this->help_email->notify('Soporte Trax', $configData['catalogo_correo'], $configData['catalogo_reintentos'], $serviceUrl, 'N/A', $store->getId());
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

            if ($catId == "" || $catId == null) {
                $catId = "Def" . $websiteCode;
            }

            $this->logger->info('GetCatalog - lee datos ' . $websiteCode);
            $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            $categories = $categoryCollection->create()->addAttributeToFilter('iws_id', $catId)->addAttributeToFilter('path', array('like' => $rootCat->getPath() . '%'));
            //Se valida si la categoría existe
            $arrayCategories = array();
            $existe = 0;

            if ($categories->getSize()) {
                $collection = $objectManager->create('Magento\Catalog\Model\Category');
                $categoryTmp = $collection->load($categories->getFirstItem()->getId(), $storeId);
                $existe = 1;
            } else {
                $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
                /// Add a new sub category under root category
                $categoryTmp = $categoryFactory->create();
                $categoryTmp->setIsActive(false);
            }
            //Se asocian campos
            $name = ucfirst($catalog->Category->Description);
            if ($name == "" || $name == null) {
                $name = "default";
            }
            $url = strtolower(rand(0, 1000) . '-' . $name . '-' . $catId . '-' . $rootNodeId . '-' . $storeId . '-' . $key);
            $cleanurl = html_entity_decode(strip_tags($url));            
            $categoryTmp->setName($name);
            $categoryTmp->setIncludeInMenu(true);
            $categoryTmp->setData('description', $catalog->Category->Description);
            if ($existe == 0) {
                try {
                    $categoryTmp->setUrlKey($cleanurl);
                } catch (Exception $e) {
                    $this->logger->info('GetCatalog - Key ya existe Error: ' . $e->getMessage());
                }
                $categoryCollection1 = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
                $categoriesAll = $categoryCollection1->create()->addAttributeToFilter('iws_id', 'all_categories')->addAttributeToFilter('parent_id', array('eq' => $rootNodeId));
                if ($categoriesAll->getSize()) {
                    foreach ($categoriesAll as $key => $data) {
                        //Se asocia categoria
                        if ($data->getParentId() == $rootCat->getId()) {
                            $categoryTmp->setPath($data->getPath());
                            $categoryTmp->setParentId($data->getId());
                            $this->logger->info('GetCatalog - Asigna parent: ' . $data->getPath());
                            break;
                        }
                    }
                } else {
                    $categoryTmp->setPath($rootCat->getPath());
                    $categoryTmp->setParentId($rootCat->getId());
                    $this->logger->info('GetCatalog - No asigna parent: ' . $rootCat->getId());
                }
            }
            if ($catalog->Category->Subcategories && count($catalog->Category->Subcategories) > 0) {
                $categoryTmp->setIsAnchor(0);
                $categoryTmp->setPageLayout('1column');
            }
            //Corrige error de layout
            if ($categoryTmp->getCustomLayoutUpdate() == '1column') {
                $categoryTmp->setCustomLayoutUpdate('');
            }
            try {
                $categoryTmp->setIwsId($catId);
                $categoryTmp->setStoreId($storeId);
                $categoryTmp->save();
                $this->logger->info('GetCatalog - Guarda categoria: ' . $categoryTmp->getId());
                $this->logger->info('GetCatalog - Categoria Padre: ' . $categoryTmp->getParentId());
                $this->logger->info('GetCatalog - Categoria Path Padre: ' . $categoryTmp->getPath());
                $arrayCategories[$categoryTmp->getId()] = $categoryTmp->getId();
                //Se valida si tiene subcategorias
                if ($catalog->Category->Subcategories && count($catalog->Category->Subcategories) > 0) {
                    $arrayCategories = $this->loadSubcategoriesData($catalog->Category->Subcategories, $websiteCode, $store, $storeId, $categoryTmp->getId(), $arrayCategories);
                    $rootCat->load($rootNodeId);
                }
                //Se valida producto y se asocia a categoria
                $product_id = $this->loadProductsData($catalog, $objectManager, $storeId, $websiteId, $arrayCategories, $configData);
                //Se asocian categorias a productos
                if ($product_id) {
                    $allProducts[$product_id] = $product_id;
                    foreach ($this->getProcessedProductsInCrocsEvent() as $key => $object)
                    {
                        $this->logger->info('Processed Products In Crocs Event: ' . $object['id']);
                        $allProducts[$object['id']] = $object['id'];
                        if($object['restore_price']){
                            $this->restorePriceAttributeForProduct($object['store_id'], $object['id'], $object['price']);
                        }
                    }
                }
                $this->setProcessedProductsInCrocsEvent([]);
                array_push($allCategories, $arrayCategories);
            } catch (Exception $e) {
                $this->logger->info('GetCatalog - Se ha producido un error al guardar la categoria ' . $categoryTmp->getId() . '. Error: ' . $e->getMessage());
            }
        }
        $this->loadCatalogSales($configData, $websiteCode, $store, $storeId);
        //Se verifican categorias no retornadas en el servicio y se deshabilitan
        $newArrayCategory = array();
        foreach ($allCategories as $key => $categoryData) {
            foreach ($categoryData as $key1 => $category) {
                $newArrayCategory[$key1] = $category;
            }
        }
        $this->checkCategories($newArrayCategory, $store->getRootCategoryId(), $storeId);
        //Se verifican productos no retornados en el servicio y se deshabilitan
        $this->checkProducts($allProducts, $storeId);
    }

    //Carga la información de precios
    public function loadCatalogSalesData($data, $websiteCode, $store, $storeId, $configData)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $errors = '';
        //Se recorre array
        foreach ($data as $key => $catalog) {
            $this->logger->info('GetCatalogSalesData - Lee datos. Website: ' . $websiteCode);
            $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
            $products = $productFactory->create();
            //Se carga producto por SKU
            $product = $products->setStoreId($storeId)->loadByAttribute('sku', $catalog->Sku);
            if (!$product || $product->getStatus() != 1) {
                $this->logger->info('GetCatalogSalesData - Se ha producido un error al actualizar los datos del producto con SKU ' . $catalog->Sku . ' en el Website: ' . $websiteCode . '. El producto no existe');
            } else {

                $attributes = array();
                $style = 'style="border:1px solid"';
                // Set Price
                if ($configData['product_price']) {
                    $price = $catalog->Price->UnitPrice;
                    if($price==''||empty($price)||$price==0){
                        $errors .= '<tr>';
                        $errors .= '<td '.$style.' >'.$catalog->Sku.'</td>';
                        $errors .= '<td '.$style.' >'.$websiteCode.'</td>';
                        $errors .= '<td '.$style.' >'.$catalog->Price->UnitPrice.'</td>';
                        $errors .= '<td '.$style.' >Precio</td>';
                        $errors .= '</tr>';
                    }else{
                        $attributes['Price'] = $catalog->Price->UnitPrice;
                    }
                }

                try {
                    $this->setStoreViewDataProduct($product->getId(), $catalog->Sku, $storeId, $attributes);
                    $this->logger->info('GetCatalogSalesData - Se actualizan datos del producto con SKU ' . $catalog->Sku . ' en el Website: ' . $websiteCode);
                } catch (Exception $e) {
                    $this->logger->info('GetCatalogSalesData - Se ha producido un error al actualizar los datos del producto con SKU ' . $catalog->Sku . ' en el Website: ' . $websiteCode . '. Error: ' . $e->getMessage());
                }
            }
        }
        if($errors!=''){
            $this->notifyErrrorPrice($errors);
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

            if ($catId == "" || $catId == null) {
                $catId = "SubDef" . $websiteCode;
            }

            //Se carga la categoria por atributo
            $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            $categories = $categoryCollection->create()->addAttributeToFilter('iws_id', $catId)->addAttributeToFilter('parent_id', array('eq' => $rootNodeId));
            $existe = 0;
            //Se valida si la categoría existe
            if ($categories->getSize()) {
                $collection = $objectManager->create('Magento\Catalog\Model\Category');
                $categoryTmp = $collection->load($categories->getFirstItem()->getId(), $storeId);
                $existe = 1;
            } else {
                $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
                /// Add a new sub category under root category
                $categoryTmp = $categoryFactory->create();
                $categoryTmp->setIsActive(false);
            }
            /// Get Root Category
            $rootCat = $objectManager->get('Magento\Catalog\Model\Category');
            $cat_info = $rootCat->load($rootNodeId);

            //Se asocian campos
            $name = ucfirst($catalog->Description);
            if ($name == "" || $name == null) {
                $name = "default";
            }
            $url = strtolower(rand(0, 1000) . '-' . $name . '-' . $catId . '-' . $rootNodeId . '-' . $storeId . '-' . $key);
            $cleanurl = html_entity_decode(strip_tags($url));
            $categoryTmp->setName($name);            
            $categoryTmp->setData('description', $catalog->Description);
            if ($existe == 0) {
                try {
                    $categoryTmp->setUrlKey($cleanurl);
                } catch (Exception $e) {
                    $this->logger->info('GetCatalog - Key ya existe Error: ' . $e->getMessage());
                }
                $categoryTmp->setParentId($rootCat->getId());
                $categoryTmp->setPath($rootCat->getPath());
            }
            try {
                $categoryTmp->setIwsId($catId);
                $categoryTmp->setStoreId($storeId);
                $categoryTmp->save();
                $this->logger->info('GetCatalog - Guarda subcategoria: ' . $categoryTmp->getId());
                $this->logger->info('GetCatalog - Categoria Padre: ' . $categoryTmp->getParentId());
                $this->logger->info('GetCatalog - Categoria Path Padre: ' . $categoryTmp->getPath());
                $arrayCategories[$categoryTmp->getId()] = $categoryTmp->getId();
            } catch (Exception $e) {
                $this->logger->info('GetCatalog - Se ha producido un error al guardar la subcategoria ' . $categoryTmp->getId() . '. Error: ' . $e->getMessage());
            }
        }
        return $arrayCategories;
    }

    //Carga la información de los productos
    public function loadProductsData($catalog, $objectManager, $storeId, $websiteId, $categoryIds, $configData)
    {
        // Crocs Logic
        $configurableProductIsEnabled = $this->configurableProduct->getIsModuleEnabled($storeId);
        if ($configurableProductIsEnabled) {
            $configurableSku = $this->configurableProduct->getConfigurableSku($catalog->Mpn, $storeId);
            if ($configurableSku) {
                $sizes = $this->configurableProduct->getSizes($catalog->Mpn, $storeId);
                // If it is multi size
                if (count($sizes) > 1) {
                    $catalog->Sku = $this->configurableProduct->getSkuWithPrefixIfNeeded($catalog->Sku . $this->configurableProduct->getSeparator($storeId) . $sizes[0], $storeId);
                } else {
                    $catalog->Sku = $this->configurableProduct->getSkuWithPrefixIfNeeded($catalog->Sku, $storeId);
                }
            } else {
                $catalog->Sku = $this->configurableProduct->getSkuWithPrefixIfNeeded($catalog->Sku, $storeId);
            }
        }

        $isNewProduct = false;
        try {
            $errors = '';
            $product = $this->productRepository->get($catalog->Sku, true, $storeId, true);
            $style = 'style="border:1px solid"';
            $this->logger->debug('UpdateSku: ' . $product->getSku());
        } catch (NoSuchEntityException $e) {
            $this->logger->debug('CreateSku: ' . $catalog->Sku);
            $isNewProduct = true;
        }

        if ($isNewProduct) {
            $product = $objectManager->create('\Magento\Catalog\Model\Product');
            $product->setStoreId($storeId)->setSku($catalog->Sku); // Set your sku here
            $product->setStatus(0); // Status on product enabled/ disabled 1/0
            $url = strtolower(rand(0, 1000) . '-' . $catalog->Description . '-' . $catalog->Sku . '-' . $storeId);
            $cleanurl = html_entity_decode(strip_tags($url));
            try {
                $product->setUrlKey($cleanurl);
            } catch (Exception $e) {
                $this->logger->info('GetCatalog - Key ya existe Error: ' . $e->getMessage());
            }
            $iwsDescription = explode("- ", $catalog->Description);
            $name = $iwsDescription[0];
            $description = "";
            if (isset($iwsDescription[1])) {
                $name .= $iwsDescription[1];
            }
            if (isset($iwsDescription[2])) {
                $name .= $iwsDescription[2];
                for ($i = 3; $i < count($iwsDescription); $i++) {
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
            if ($configData['product_name']) {
                $product->setName($name); // Name of Product
            }
            if ($configData['product_description']) {
                $product->setDescription($description); // Description of Product
            }
            $product->setAttributeSetId($configData['attribute_id']); // Attribute set id
            $product->setWebsiteIds($websiteIds);
            $this->logger->info('GetCatalog - Se asocia website a producto: ' . $websiteId);
            $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
            $product->setTaxClassId($configData['tax_id']); // Tax class id
            $this->logger->info('GetCatalog - Atribute id: ' . $configData['attribute_id']);
            $this->logger->info('GetCatalog - Tax id: ' . $configData['tax_id']);
            if ($configData['product_mpn']) {
                $product->setData('mpn', $catalog->Mpn); // Add Mpn
                $product->setCustomAttribute('mpn', $catalog->Mpn); // add Mpn
            }
            $product->setData('iws_type',$catalog->Type);
            $product->setCustomAttribute('iws_type',$catalog->Type);
            switch ($catalog->Type) {
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
                case 'Kit':
                    $product->setTypeId('simple');
                    break;
            } // type of product (simple/virtual/downloadable/configurable)
            //Set product dimensions
            if (isset($catalog->Freight)) {
                if (isset($catalog->Freight->Item)) {
                    if ($configData['product_weight']) {
                        $product->setWeight($catalog->Freight->Item->Weight);
                    }
                    if ($configData['product_length']) {
                        $product->setData('length', $catalog->Freight->Item->Length);
                        $product->setData('ts_dimensions_height', $catalog->Freight->Item->Height);
                        $product->setCustomAttribute('ts_dimensions_length', $catalog->Freight->Item->Length);
                    }
                    if ($configData['product_width']) {
                        $product->setData('width', $catalog->Freight->Item->Width);
                        $product->setData('ts_dimensions_width', $catalog->Freight->Item->Width);
                        $product->setCustomAttribute('ts_dimensions_width', $catalog->Freight->Item->Width);
                    }
                    if ($configData['product_height']) {
                        $product->setData('height', $catalog->Freight->Item->Height);
                        $product->setData('ts_dimensions_height', $catalog->Freight->Item->Height);
                        $product->setCustomAttribute('ts_dimensions_height', $catalog->Freight->Item->Height);
                    }
                }
            }
            //Set Price
            if ($configData['product_price']) {

                if($catalog->Price->UnitPrice==''||empty($catalog->Price->UnitPrice)||$catalog->Price->UnitPrice==0){
                    $errors .= '<tr>';
                    $errors .= '<td '.$style.' >'.$catalog->Sku.'</td>';
                    $errors .= '<td '.$style.' >'.$websiteId.'</td>';
                    $errors .= '<td '.$style.' >'.$catalog->Price->UnitPrice.'</td>';
                    $errors .= '<td '.$style.' >Precio</td>';
                    $errors .= '</tr>';
                }else{
                    $product->setPrice($catalog->Price->UnitPrice);
                }

            }

            try {
                $product->save();
                $this->logger->info('GetCatalog - Se CREA producto ' . $product->getSku() . ' en el store: ' . $storeId);
                $productId = $product->getId();
            } catch (Exception $e) {
                $this->logger->info('GetCatalog - Se ha producido un error al CREAR producto con sku ' . $catalog->Sku . '. Error: ' . $e->getMessage());
                return false;
            }
        }
        //Save product by store
        else {
            //Set Categories
            $categoriesData = [];
            foreach ($categoryIds as $categoryId)
                $categoriesData[] = ['product_id' => $product->getId(), 'category_id' => $categoryId, 'position' => 0];

            try {
                $this->_saveProductCategories($categoriesData);
            } catch (Exception $e) {
                $this->logger->info('GetCatalog - Se ha producido un Error: ' . $e->getMessage());
            }

            //Set WebSites
            $websitesData[] = ['product_id' => $product->getId(), 'website_id' => $websiteId];

            try {
                $this->_saveProductWebsites($websitesData);
            } catch (Exception $e) {
                $this->logger->info('GetCatalog - Se ha producido un Error: ' . $e->getMessage());
            }

            $attibutes = array();

            $iwsDescription = explode("- ", $catalog->Description);
            $name = $iwsDescription[0];
            $description = "";
            if (isset($iwsDescription[1])) {
                $name .= $iwsDescription[1];
            }
            if (isset($iwsDescription[2])) {
                $name .= $iwsDescription[2];
                for ($i = 3; $i < count($iwsDescription); $i++) {
                    $description .= $iwsDescription[$i];
                }
            }

            // Set Name
            if ($configData['product_name']) {
                $attibutes['Name'] = $name;
            }

            // Set Description
            if ($configData['product_description']) {
                $attibutes['Description'] = $description;
            }

            //Set Tax Class
            $attibutes['tax_class_id'] = $configData['tax_id'];

            // Set Price
            if ($configData['product_price']) {

                if($catalog->Price->UnitPrice==''||empty($catalog->Price->UnitPrice)||$catalog->Price->UnitPrice==0){
                    $errors .= '<tr>';
                    $errors .= '<td '.$style.' >'.$catalog->Sku.'</td>';
                    $errors .= '<td '.$style.' >'.$websiteId.'</td>';
                    $errors .= '<td '.$style.' >'.$catalog->Price->UnitPrice.'</td>';
                    $errors .= '<td '.$style.' >Precio</td>';
                    $errors .= '</tr>';
                }else{
                    $attibutes['Price'] = $catalog->Price->UnitPrice;
                }
            }

            //Set product dimensions
            if (isset($catalog->Freight)) {
                if (isset($catalog->Freight->Item)) {

                    if ($configData['product_width']) {
                        $attibutes['Width'] = $catalog->Freight->Item->Width;
                        $attibutes['ts_dimensions_width'] = $catalog->Freight->Item->Width;
                    }
                    if ($configData['product_height']) {
                        $attibutes['Height'] = $catalog->Freight->Item->Height;
                        $attibutes['ts_dimensions_height'] = $catalog->Freight->Item->Height;
                    }
                    if ($configData['product_length']) {
                        $attibutes['ts_dimensions_length'] = $catalog->Freight->Item->Length;
                    }
                    if ($configData['product_weight']) {
                        $attibutes['Weight'] = $catalog->Freight->Item->Weight;
                    }
                }
            }

            try {
                $this->logger->info('GetCatalog - Product:\n');
                $this->setStoreViewDataProduct($product->getId(), $catalog->Sku, $storeId, $attibutes);
                $this->logger->info('GetCatalog - Se ACTUALIZA producto ' . $product->getSku() . ' en el store: ' . $storeId);
                $productId = $product->getId();
            } catch (Exception $e) {
                $this->logger->info('GetCatalog - Se ha producido un error al ACTUALIZAR producto con sku ' . $catalog->Sku . '. Error: ' . $e->getMessage());
                return false;
            }
        }

        // Call Crocs Functionality
        if ($productId && $configurableProductIsEnabled)
        {
            $this->logger->debug('Dispatch Event intcomex_crocs_catalog_product_save_before ProductId: ' . $productId);
            $this->eventManager->dispatch(
                'intcomex_crocs_catalog_product_save_before',
                ['product' => $product, 'config_data' => $configData, 'sender_context' => $this]
            );
        }

        if ($errors!='') {
            $this->notifyErrrorPrice($errors);
        }

        return $productId;
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
            ->addAttributeToFilter('path', array('like' => $rootCat->getPath() . '%'));

        foreach ($categories as $category) {
            if ((!array_key_exists($category->getId(), $allCategories) && $category->getIwsId() != '' && $category->getIwsId() != 'N/A' && $category->getIwsId() != 'all_categories' && $category->getIsActive()) || ($category->getName() == '' || !$category->getName())) {
                $categoryFactoryData = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
                $categoryTmp = $categoryFactoryData->create()->setStoreId($storeId)->load($category->getId());
                //Se deshabilita categoría            
                $categoryTmp->setIsActive(false);
                try {
                    $categoryTmp->save();
                    $this->logger->info('GetCatalog - Se deshabilita categoria ' . $categoryTmp->getId());
                } catch (Exception $e) {
                    $this->logger->info('GetCatalog - Se ha producido un error al deshabilitar la categoria ' . $categoryTmp->getId() . '. Error: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Checks and disables products present in Store but not in IWS.
     *
     * @param $allProducts
     * @param $storeId
     */
    public function checkProducts($allProducts, $storeId)
    {
        $productsToDisabled = [];
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $productFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $count = count($this->product_iws_not);
        $this->logger->info('checkProducts - Listas de productos');
        $this->logger->info(print_r($allProducts,true));

        // Search all products in store
        $products = $productFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', ['neq' => 'configurable'])
            ->addStoreFilter($storeId);

        // Validates if product is in IWS
        foreach ($products as $product) {
            if (!array_key_exists($product->getId(), $allProducts)) {
                if ((int)$product->getStatus() === \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
                    $productsToDisabled[] = $product->getId();
                    $this->product_iws_not[$count]['sku'] = $product->getSku();
                    $this->product_iws_not[$count]['store'] = $storeId;
                    $this->product_iws_not[$count]['status'] = $product->getStatus();
                    $this->logger->info('GetCatalog - Se añade producto para deshabilitar ' . $product->getName());
                } else {
                    $this->logger->info('GetCatalog - El producto ya esta deshabilitado ' . $product->getName());
                }
            }
            $count++;
        }

        // Products to disable that are not present in IWS
        if ($productsToDisabled) {
            $this->logger->info('GetCatalog - Productos a deshabilitar :: ' . print_r($productsToDisabled, true));
            try {
                $attributes = ['status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED];
                $this->productAction->updateAttributes($productsToDisabled, $attributes, $storeId);
                $this->logger->info('GetCatalog - Productos deshabilitados!');
            } catch (\Exception $e) {
                $this->logger->info('GetCatalog - Se ha producido un error al deshabilitar los productos: ' . $e->getMessage());
            }
        }

        /*// se envia la notificacion al admin de productos que no llegan desde IWS
        if(count($products_noiws) > 0){
            $this->notifyProductNoIWS($products_noiws, $storeId);
        }else{
            $this->logger->info('GetCatalog - No hay productos nuevos en Magento');
        }*/
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
    public function setStoreViewDataProduct($productId, $sku, $storeId, $attibutes)
    {
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        $product        = $productFactory->create();

        $productResourceModel = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product');
        $productResourceModel->load($product, $productId);

        $product->setStoreId($storeId);

        $this->logger->info('Data Store view- Product id:'.$product->getId());
        $this->logger->info(print_r($attibutes,true));

        foreach ($attibutes as $name => $value) {

            $method = 'set' . $name;
            $product->$method($value);

            try {
                $productResourceModel->saveAttribute($product, strtolower($name));

                if($name == 'Price'){
                    //logs prices
                    $this->logger_price->info('Automatic Price - Product id: ' . $productId );
                    $this->logger_price->info('Automatic Price - Product sku: ' .  $product->getSku());
                    $this->logger_price->info('Automatic Price - Product old ' . $product->getData('price') );
                    $this->logger_price->info('Automatic Price - Product new ' . $value );
    
                }

            } catch (Exception $e) {
                $this->logger->info('GetCatalog - Se ha producido un error setStoreViewDataProduct Error: ' . $e->getMessage());
            }        

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

        return $this;
    }

    /**
     * Save product categories.
     *
     * @param array $categoriesData
     * @author GDCP <german.cardenas@intcomex.com>
     * @return $this
     */
    protected function _saveProductCategories(array $categoriesData)
    {
        static $tableName = null;

        if (!$tableName) {
            $tableName = $this->_resourceFactory->create()->getProductCategoryTable();
        }

        if ($categoriesData) {
            $this->_connection->insertOnDuplicate($tableName, $categoriesData, ['product_id', 'category_id']);
        }

        return $this;
    }

    /**
     * @param $errors
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function notifyErrrorPrice($errors)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('\Intcomex\CustomLog\Helper\Email');

        $templateId  = $this->scopeConfig->getValue('customlog/general/email_template');
        $extraError = $this->scopeConfig->getValue('customlog/general/mensaje_alerta');
        $email = explode(',',$this->scopeConfig->getValue('customlog/general/correos_alerta'));

        $variables = array(
            'mensaje' => $extraError,
            'body' => $errors
        );
        foreach($email as $key => $value){
            if(!empty($value)){
                $helper->notify($value,$variables,$templateId);
            }
        }
    }

    /**
     * @param $products
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function notifyProductNoIWS($products)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $list_products = array();

        foreach ($products as $product) {
            $manager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
            $store = $manager->getStore($product['store']);
            $product['storeName'] = $store->getName();
            $list_products[] = $product;
        }

        $this->logger->info('GetCatalog - Se envia Data de productos del store ' . print_r($list_products, true));

        $text_mail = $this->scopeConfig->getValue('trax_catalogo/catalogo_general/text_email_product_iws', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $this->help_email->notifyProductIWS('Soporte Whitelabel',$list_products, $text_mail);

        $this->logger->info('GetCatalog - Se envia notificacion de productos');
    }

     /**
      * @param Product $product
      * @return int|void|null
      */
    private function restorePriceAttributeForProduct($storeId, $productId, $price)
    {
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            $sql = 'INSERT INTO '. $connection->getTableName('catalog_product_entity_decimal');
            $sql .= ' (attribute_id, store_id, value, row_id) ';
            $sql .= 'VALUES(
                (SELECT attribute_id FROM '.$connection->getTableName('eav_attribute').' AS eav WHERE eav.entity_type_id = 4 AND eav.attribute_code = "price"),
                '.$storeId.',
                '.$price.',
                (SELECT row_id FROM '.$connection->getTableName('catalog_product_entity').' AS cpe WHERE cpe.entity_id = '.$productId.')
            );';
            $this->logger->debug('SQL - restore price: ' . $sql);
            $connection->query($sql);
        }catch (\Exception $e){
            $this->logger->debug('SQL - execute restore price failed: ' . $e->getMessage());
        }
    }
}
