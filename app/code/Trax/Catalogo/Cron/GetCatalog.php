<?php
namespace Trax\Catalogo\Cron;
use \Psr\Log\LoggerInterface;

class GetCatalog {

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';

	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';

	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';

    const DATOS_TRAX = 'trax_catalogo/catalogo_general/datos_iws';

    const DATOS_SALES_TRAX = 'trax_catalogo/catalogo_general/datos_sales_iws';

    const DATOS_IMAGES_TRAX = 'trax_catalogo/catalogo_general/datos_images_iws';
	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    protected $logger;

    protected  $productRepository;     

    public function __construct(LoggerInterface $logger, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
    }

/**
   * Write to system.log
   *
   * @return void
   */

    public function execute() 
    {
        //Se declaran variables de la tierra
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        
        //Se obtienen todos los websites 
        $websites = $storeManager->getWebsites();
        foreach ($websites as $key => $website) {
            //Se obtienen parametros de configuración por Store
            $configData = $this->getConfigParams($storeScope, $website->getCode());    
            //Se carga el servicio por curl
            if($configData['datos_iws']){        
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        $serviceUrl = $this->getServiceUrl($configData, 1, $store->getCode());
                        if($serviceUrl){ 
                            //Se conecta al servicio 
                            $data = $this->loadIwsService($serviceUrl);
                            if($data){     
                                $this->loadCatalogData($data, $website->getCode(), $store, $store->getId(), $configData, $website->getId());
                            } else {
                                $this->logger->info('GetCatalog - Error conexión: '.$serviceUrl);
                            }   
                        } else {
                            $this->logger->info('GetCatalog - No se genero url del servicio en el website: '.$website->getCode().' con store '.$store->getCode());
                        }
                    }
                }
            } else {
                $this->logger->info('GetCatalog - El website '.$website->getCode().' con store '.$website->getCode().' no tiene habilitada la conexión con IWS para obtener el catalogo con información general de los productos');
                $this->loadCatalogSales($configData, $website->getCode(), $website->getDefaultStore(), $website->getDefaultStoreId());
            }
        }

    }

    //Se genera metodo para consultar servicio de get catalog sales data
    public function loadCatalogSales($configData, $websiteCode, $store, $storeId) 
    {
        if($configData['datos_sales_iws']){
            $serviceUrl = $this->getServiceUrl($configData, 2, $store->getCode());
            if($serviceUrl){
                $data = $this->loadIwsService($serviceUrl);
                if($data){                    
                    $this->loadCatalogSalesData($data, $websiteCode, $store, $storeId);
                } else {
                    $this->logger->info('GetCatalogSalesData - Error conexión: '.$serviceUrl);
                }
            } else {
                $this->logger->info('GetCatalogSalesData - El website '.$websiteCode.' con store '.$storeId.' no tiene habilitada la conexión con IWS');
            }
        } else {
            $this->logger->info('GetCatalogSalesData - El website '.$websiteCode.' con store '.$storeId.' no tiene habilitada la conexión con IWS para obtener precios e inventario de los productos');
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
        $configData['datos_iws'] = $this->scopeConfig->getValue(self::DATOS_TRAX, $storeScope, $websiteCode);
        $configData['datos_sales_iws'] = $this->scopeConfig->getValue(self::DATOS_SALES_TRAX, $storeScope, $websiteCode);
        $configData['datos_images_iws'] = $this->scopeConfig->getValue(self::DATOS_IMAGES_TRAX, $storeScope, $websiteCode);
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
        $this->logger->info('GetCatalog - status code: '.$status_code);
        $this->logger->info('GetCatalog - '.$serviceUrl);
        $this->logger->info('GetCatalog - curl errors: '.$curl_errors);
        if ($status_code == '200'){
            return json_decode($resp);
        }
        return false;

    }

    //Carga la información del catalogo
    public function loadCatalogData($data, $websiteCode, $store, $storeId, $configData, $websiteId) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        //Se recorre array        
        $allCategories = array();
        $allProducts = array();
        foreach ($data as $key => $catalog) {
            $this->logger->info('GetCatalog - lee datos '.$websiteCode);
            //Se carga la categoria por atributo
            
            $rootNodeId = $store->getRootCategoryId();
            /// Get Root Category
            $rootCat = $objectManager->get('Magento\Catalog\Model\Category');
            $cat_info = $rootCat->load($rootNodeId);
            echo "root category: ".$rootNodeId."<br>";
            $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            $categories = $categoryCollection->create()->addAttributeToFilter('iws_id',$catalog->Category->CategoryId)->addAttributeToFilter('path',array('like' => $rootCat->getPath().'%'));
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
            }
            //Se asocian campos
            $name=ucfirst($catalog->Category->Description);
            $url=strtolower($catalog->Category->Description.'-'.$catalog->Category->CategoryId.'-'.$rootNodeId.'-'.$storeId.'-'.$key);
            $cleanurl = html_entity_decode(strip_tags($url));
            $categoryTmp->setName($name);
            $categoryTmp->setIsActive(true);
            $categoryTmp->setUrlKey($cleanurl);
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
            $categoryTmp->setIwsId($catalog->Category->CategoryId);
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
                }
                //Se valida producto y se asocia a categoria
                $product_id = $this->loadProductsData($catalog, $objectManager, $storeId, $websiteId);
                //Se asocian categorias a productos
                if($product_id){
                    $CategoryLinkRepository = $objectManager->get('\Magento\Catalog\Api\CategoryLinkManagementInterface');
                    $CategoryLinkRepository->assignProductToCategories($product_id, $arrayCategories);
                    $this->logger->info('GetCatalog - Se asocia producto a categoria. SKU: '.$product_id);
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
        $this->checkCategories($newArrayCategory, $store->getRootCategoryId());
        //Se verifican productos no retornados en el servicio y se deshabilitan
        $this->checkProducts($allProducts, $store->getRootCategoryId());
    }

    //Carga la información de precios e inventario del catalogo
    public function loadCatalogSalesData($data, $websiteCode, $store, $storeId) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();    
        //Se recorre array
        foreach ($data as $key => $catalog) {
            $this->logger->info('GetCatalogSalesData - Lee datos. Website: '.$websiteCode);
            $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
            $products = $productFactory->create();
            //Se carga producto por SKU
            $product = $products->loadByAttribute('sku', $catalog->Sku);            
            if(!$product || $product->getStatus()!=1){
                $this->logger->info('GetCatalogSalesData - Se ha producido un error al actualizar los datos del producto con SKU '.$catalog->Sku.' en el Website: '.$websiteCode.'. El producto no existe');
            } else {
                $product->setPrice($catalog->Price->UnitPrice);
                if($catalog->InStock == 0){
                    $stock = 0;
                } else {
                    $stock = 1;
                }
                $product->setStockData(
                    array(
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 1,
                        'is_in_stock' => $stock,
                        'min_sale_qty' => 1,
                        'qty' => $catalog->InStock
                    )
                );
                try{
                    $product->save();
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
            //Se carga la categoria por atributo
            $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            $categories = $categoryCollection->create()->addAttributeToFilter('iws_id',$catalog->CategoryId)->addAttributeToFilter('parent_id',array('eq' => $rootNodeId));
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
            }
            /// Get Root Category
            $rootCat = $objectManager->get('Magento\Catalog\Model\Category');
            $cat_info = $rootCat->load($rootNodeId);

            //Se asocian campos
            $name=ucfirst($catalog->Description);
            $url=strtolower($catalog->Description.'-'.$catalog->CategoryId.'-'.$rootNodeId.'-'.$storeId.'-'.$key);
            $cleanurl = html_entity_decode(strip_tags($url));
            $categoryTmp->setName($name);
            $categoryTmp->setIsActive(true);
            $categoryTmp->setUrlKey($cleanurl);
            $categoryTmp->setData('description', $catalog->Description);
            if($existe==0){
                $categoryTmp->setParentId($rootCat->getId());
                $categoryTmp->setPath($rootCat->getPath());
            }
            $categoryTmp->setIwsId($catalog->CategoryId);
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
    public function loadProductsData($catalog, $objectManager, $storeId, $websiteId) 
    {        
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        $products = $productFactory->create();
        $product = $products->loadByAttribute('sku', $catalog->Sku);
        if(!$product){
            $product = $objectManager->create('\Magento\Catalog\Model\Product');
            $product->setSku($catalog->Sku); // Set your sku here
        } 
        $url=strtolower($catalog->Description.'-'.$catalog->Sku.'-'.$storeId);
        $cleanurl = html_entity_decode(strip_tags($url));
        $product->setUrlKey($cleanurl);
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
        $product->setName($name); // Name of Product        
        $product->setDescription($description); // Description of Product
        $product->setAttributeSetId(4); // Attribute set id
        $product->setStoreId($storeId);
        $product->setWebsiteIds(array($websiteId));
        $this->logger->info('GetCatalog - Se asocia website a producto: '.$websiteId);
        $product->setStatus(1); // Status on product enabled/ disabled 1/0
        $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
        $product->setTaxClassId(0); // Tax class id
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
        try{
            $product->save();
            $this->logger->info('GetCatalog - Se guarda producto '.$product->getSku().' en el store: '.$storeId);
            return $product->getSku();
        } catch (Exception $e){
            $this->logger->info('GetCatalog - Se ha producido un error al guardar la subcategoria '.$categoryTmp->getId().'. Error: '.$e->getMessage());
            return false;
        }
    }

    //Deshabilita las categorias no retornadas en el servicio
    public function checkCategories($allCategories, $rootNodeId) 
    {   
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
        $appState = $objectManager->get('\Magento\Framework\App\State');
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryFactory->create()                              
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('parent_id',array('eq' => $rootNodeId));
        
        foreach ($categories as $category){
            if(!array_key_exists($category->getId(), $allCategories) && $category->getIwsId()!= '' && $category->getIwsId()!= 'N/A' && $category->getIwsId()!= 'all_categories' &&$category->getIsActive()){
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
    public function checkProducts($allProducts, $rootNodeId) 
    {   
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
        $appState = $objectManager->get('\Magento\Framework\App\State');
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryFactory->create() ->load($rootNodeId);
        $products = $categories->getProductCollection()
                         ->addAttributeToSelect('*');
        
        foreach ($products as $product){
            if(!array_key_exists($product->getSku(), $allProducts) && $product->getStatus() != 0){
                $productFactoryData = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
                $products = $productFactoryData->create();
                $productTmp = $products->loadByAttribute('sku', $product->getSku());                
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

}