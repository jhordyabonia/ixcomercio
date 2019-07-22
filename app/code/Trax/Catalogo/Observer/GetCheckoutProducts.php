<?php

namespace Trax\Catalogo\Observer;
use \Psr\Log\LoggerInterface;

class GetProducts implements \Magento\Framework\Event\ObserverInterface
{

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_catalogo/catalogo_general/apuntar_a';

	const URL_DESARROLLO = 'trax_catalogo/catalogo_general/url_desarrollo';

	const URL_PRODUCCION = 'trax_catalogo/catalogo_general/url_produccion';

    const DATOS_TRAX = 'trax_catalogo/catalogo_general/datos_iws';

    const DATOS_SALES_TRAX = 'trax_catalogo/catalogo_general/datos_sales_iws';

    const DATOS_IMAGES_TRAX = 'trax_catalogo/catalogo_general/datos_images_iws';

    const DATOS_CATEGORIAS_TRAX = 'trax_catalogo/catalogo_general/categorias_iws';

    const CATALOGO_REINTENTOS = 'trax_catalogo/catalogo_general/catalogo_reintentos';

    const CATALOGO_CORREO = 'trax_catalogo/catalogo_general/catalogo_correo';
    
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
    public function __construct(LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Trax\Catalogo\Helper\Email $email
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $email;
	}
	
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		/*$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		//Se obtienen parametros de configuración por Store
		$configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode());
        //Se obtiene lista de sku
        if($configData['categorias_iws']==1){
            $skuList = $this->getSkuList($observer->getEvent());
            //Se obtiene url del servicio
            $serviceUrl = $this->getServiceUrl($configData, $skuList);
            //Se carga el servicio por curl
            if($configData['datos_iws']){
                if($serviceUrl){
                    $this->beginCatalogLoad($configData, $storeManager, $serviceUrl, $objectManager, 0); 
                } else {
                    $this->logger->info('GetProducts - No se genero url del servicio en el website: '.$website->getCode().' con store '.$website->getDefaultStoreId());
                }
            }
        }*/
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
        $configData['categorias_iws'] = $this->scopeConfig->getValue(self::DATOS_CATEGORIAS_TRAX, $storeScope, $websiteCode);
        $configData['catalogo_reintentos'] = $this->scopeConfig->getValue(self::CATALOGO_REINTENTOS, $storeScope, $websiteCode);
        $configData['catalogo_correo'] = $this->scopeConfig->getValue(self::CATALOGO_CORREO, $storeScope, $websiteCode);
        return $configData;

    }

	public function getSkuList($event) 
	{
		$category = $event->getData('category');
		$productCollection = $category->getProductCollection();
		$i = 0;
		$len = $category->getProductCollection()->count();
		if($len>0){
			foreach($productCollection as $product){
				if ($i == 0) {
					$skuList = $product->getSku().",";
				} elseif ($i == $len - 1) {
					$skuList .= $product->getSku();
				} else {
					$skuList .= $product->getSku().",";
				}
				$i++;
			}
		} else {
			$skuList = "";
		}
        return $skuList;
    }

	public function getServiceUrl($configData, $skuList) 
	{
        if($configData['apikey'] == ''){
            $serviceUrl = false;
        } else {
            $utcTime = gmdate("Y-m-d").'T'.gmdate("H:i:s").'Z';
            $signature = $configData['apikey'].','.$configData['accesskey'].','.$utcTime;
            $signature = hash('sha256', $signature);
            $serviceUrl = $configData['url'].'getproducts?locale=en&apiKey='.$configData['apikey'].'&utcTimeStamp='.$utcTime.'&signature='.$signature.'&skusList='.$skuList.'&includePriceData=true&includeInventoryData=true'; 
        }
        return $serviceUrl;
    }

    //Función recursiva para intentos de conexión
    public function beginCatalogLoad($configData, $storeManager, $serviceUrl, $objectManager, $attempts) 
    {
        $data = $this->loadIwsService($serviceUrl);
        if($data){      
        } else {
            $this->logger->info('GetProducts - Error conexión: '.$serviceUrl);
        }
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl);
        if($data){     
            $this->loadCatalogData($data, $objectManager, $storeManager->getStore()->getStoreId());
            //Se reindexa                            
            $this->reindexData();
            //Se limpia cache
            $this->cleanCache();
            $this->logger->info('GetProducts - Se actualiza información de todos los productos');
        } else {
            if($configData['catalogo_reintentos']>$attempts){
                $this->logger->info('GetProducts - Error conexión: '.$serviceUrl);
                $this->logger->info('GetProducts - Se reintenta conexión #'.$attempts.' con el servicio: '.$serviceUrl);
                $this->beginCatalogLoad($configData, $storeManager, $serviceUrl, $objectManager, $attempts+1);
            } else{
                $this->logger->info('GetProducts - Error conexión: '.$serviceUrl);
                $this->logger->info('GetProducts - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['catalogo_correo']);
                $this->helper->notify('Soporte Trax', $configData['catalogo_correo'], $configData['catalogo_reintentos'], $serviceUrl, $store->getId());
            }
        }  
    }

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
        $this->logger->info('GetProducts- status code: '.$status_code);
        $this->logger->info('GetProducts- '.$serviceUrl);
        $this->logger->info('GetProducts- curl errors: '.$curl_errors);
        if ($status_code == '200'){
            return json_decode($resp);
        }
        return false;

    }

	public function loadCatalogData($data, $objectManager, $storeId) 
	{
        //Se recorre array
        foreach ($data as $key => $catalog) {
            //Se carga la categoria por atributo
            $this->loadProductsData($catalog, $objectManager, $storeId);
        }     
    }

	public function loadProductsData($catalog, $objectManager, $storeId) 
	{        
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        $products = $productFactory->create();
        $product = $products->loadByAttribute('sku', $catalog->Sku);
        if($product){
            $url=strtolower($catalog->Description.'-'.$catalog->Sku);
            $cleanurl = html_entity_decode(strip_tags($url));
            $product->setUrlKey($cleanurl);
            $product->setName($catalog->Description); // Name of Product
            $product->setAttributeSetId(4); // Attribute set id
            $product->setStatus(1); // Status on product enabled/ disabled 1/0
            $product->setStoreId($storeId);
            $product->setWeight(10); // weight of product
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
            //Set product dimensions
            if(isset($catalog->Freight)){
                if(isset($catalog->Freight->Package)){
                    $product->setWeight($catalog->Freight->Package->Weight);
                    $product->setLength($catalog->Freight->Package->Length);
                    $product->setWidth($catalog->Freight->Package->Width);
                    $product->setHeight($catalog->Freight->Package->Height);
                }
            }
            try{
                $product->save();
                $this->logger->info('GetProducts - Se ha actualizado la información del producto con sku: '.$catalog->Sku);
            } catch (Exception $e){
                $this->logger->info('GetProducts - Se ha actualizado la información del producto con sku: '.$catalog->Sku);
            }
        } else {
			$this->logger->info('GetProducts - No se encontro producto en magento asociado al sku: '.$catalog->Sku);
		}
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
}