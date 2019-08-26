<?php

namespace Trax\Catalogo\Observer;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use \Psr\Log\LoggerInterface;

class GetCheckoutProducts implements \Magento\Framework\Event\ObserverInterface
{

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';

	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';

	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';

    const DATOS_TRAX = 'trax_catalogo/catalogo_general/datos_iws';

    const DATOS_SALES_TRAX = 'trax_catalogo/catalogo_general/datos_sales_iws';

    const DATOS_IMAGES_TRAX = 'trax_catalogo/catalogo_general/datos_images_iws';

    const DATOS_PRODUCTOS_TRAX = 'trax_catalogo/catalogo_general/productos_iws';

    const CATALOGO_REINTENTOS = 'trax_catalogo/catalogo_general/catalogo_reintentos';

    const CATALOGO_CORREO = 'trax_catalogo/catalogo_general/catalogo_correo';

    const TAX_ID = 'trax_catalogo/catalogo_general/tax_id';

    const ATTRIBUTE_ID = 'trax_catalogo/catalogo_general/attribute_id';
    
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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Trax\Catalogo\Helper\Email $email, \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,     \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,     \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool, \Magento\Indexer\Model\IndexerFactory $indexerFactory
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $email;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
	}
	
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		//Se obtienen parametros de configuración por Store
		$configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode());
        //Se obtiene lista de sku
        if($configData['productos_iws']==1){
            $skuList = $this->getSkuList($observer->getCart());
            //Se obtiene url del servicio
            $serviceUrl = $this->getServiceUrl($configData, $skuList);
            //Se carga el servicio por curl
            if($configData['datos_iws']){
                if($serviceUrl){
                    $this->beginCatalogLoad($configData, $storeManager, $serviceUrl, $objectManager, 0); 
                } else {
                    $this->logger->info('GetProducts - No se genero url del servicio en el store: '.$storeManager->getStore()->getCode().' con store '.$storeManager->getStore()->getStoreId());
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
        $configData['datos_iws'] = $this->scopeConfig->getValue(self::DATOS_TRAX, $storeScope, $websiteCode);
        $configData['datos_sales_iws'] = $this->scopeConfig->getValue(self::DATOS_SALES_TRAX, $storeScope, $websiteCode);
        $configData['datos_images_iws'] = $this->scopeConfig->getValue(self::DATOS_IMAGES_TRAX, $storeScope, $websiteCode);
        $configData['productos_iws'] = $this->scopeConfig->getValue(self::DATOS_PRODUCTOS_TRAX, $storeScope, $websiteCode);
        $configData['catalogo_reintentos'] = $this->scopeConfig->getValue(self::CATALOGO_REINTENTOS, $storeScope, $websiteCode);
        $configData['catalogo_correo'] = $this->scopeConfig->getValue(self::CATALOGO_CORREO, $storeScope, $websiteCode);
        $configData['attribute_id'] = $this->scopeConfig->getValue(self::ATTRIBUTE_ID, $storeScope, $websiteCode);
        $configData['tax_id'] = $this->scopeConfig->getValue(self::TAX_ID, $storeScope, $websiteCode);
        return $configData;

    }

    //Función recursiva para intentos de conexión
    public function beginCatalogLoad($configData, $storeManager, $serviceUrl, $objectManager, $attempts) 
    {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl);
        if($data){     
            $this->loadProductsData($data, $objectManager, $storeManager->getStore()->getStoreId(), $configData);
        } else {
            if($configData['catalogo_reintentos']>$attempts){
                $this->logger->info('GetProducts - Error conexión: '.$serviceUrl);
                $this->logger->info('GetProducts - Se reintenta conexión #'.$attempts.' con el servicio: '.$serviceUrl);
                $this->beginCatalogLoad($configData, $storeManager, $serviceUrl, $objectManager, $attempts+1);
            } else{
                $this->logger->info('GetProducts - Error conexión: '.$serviceUrl);
                $this->logger->info('GetProducts - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['catalogo_correo']);
                $this->helper->notify('Soporte Trax', $configData['catalogo_correo'], $configData['catalogo_reintentos'], $serviceUrl, 'N/A', $storeManager->getStore()->getStoreId());
            }
        }   

    }

	public function getSkuList($cart) 
	{
		$products = $cart->getQuote()->getItems();
        $skuList = "";
        $i = 0;
		$len = count($products);
        foreach ($products as $item) {
            if ($i == 0) {
                $skuList = $item->getSku().",";
            } elseif ($i == $len - 1) {
                $skuList .= $item->getSku();
            } else {
                $skuList .= $item->getSku().",";
            }
            $i++;
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
        $this->logger->info('GetProduct- status code: '.$status_code);
        $this->logger->info('GetProduct- '.$serviceUrl);
        $this->logger->info('GetProduct- curl errors: '.$curl_errors);
        if ($status_code == '200'){
            return json_decode($resp);
        }
        return false;

    }

	public function loadProductsData($catalog, $objectManager, $storeId, $configData) 
	{    
        foreach ($catalog as $key => $data) {
            $this->loadProductData($data, $objectManager, $storeId, $configData);
        }
    }

	public function loadProductData($catalog, $objectManager, $storeId, $configData) 
	{    
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        $products = $productFactory->create();
        $product = $products->loadByAttribute('sku', $catalog->Sku);
        if($product){
            $url=strtolower($catalog->Description.'-'.$catalog->Sku.'-'.$storeId.'-'.rand(0,1000));
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
            $product->setAttributeSetId($configData['attribute_id']); // Attribute set id
            $product->setStatus(1); // Status on product enabled/ disabled 1/0
            $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
            $product->setTaxClassId($configData['tax_id']); // Tax class id
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
                if(isset($catalog->Freight->Package)){
                    $product->setWeight($catalog->Freight->Package->Weight);
                    $product->setData('length',$catalog->Freight->Package->Length);
                    $product->setData('ts_dimensions_length',$catalog->Freight->Package->Length);
                    $product->setData('width',$catalog->Freight->Package->Width);
                    $product->setData('ts_dimensions_width',$catalog->Freight->Package->Width);
                    $product->setData('height',$catalog->Freight->Package->Height);
                    $product->setData('ts_dimensions_height',$catalog->Freight->Package->Height);
                }
            }
            try{
                $product->save();
                $this->logger->info('GetProducts - Se ha actualizado la información del producto con sku: '.$catalog->Sku);
                //Se reindexa                            
                $this->reindexData();
                //Se limpia cache
                $this->cleanCache();
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
        $this->logger->info('GetProducts - Se reindexa');
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
        $this->logger->info('GetProducts - Se limpia cache');
    }
}