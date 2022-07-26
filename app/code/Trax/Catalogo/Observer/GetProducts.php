<?php

namespace Trax\Catalogo\Observer;
use \Psr\Log\LoggerInterface;

class GetProducts implements \Magento\Framework\Event\ObserverInterface
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

    const DATOS_CATEGORIAS_TRAX = 'trax_catalogo/catalogo_general/categorias_iws';

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
        if($configData['categorias_iws']==1){
            $skuList = $this->getSkuList($observer->getCategory());
            //Se obtiene url del servicio
            $serviceUrl = $this->getServiceUrl($configData, $skuList);
            //Se carga el servicio por curl
            if($configData['datos_iws']){
                if($serviceUrl){
                    $this->beginCatalogLoad($configData, $storeManager->getStore()->getStoreId(), $serviceUrl, $objectManager, 0); 
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
        $configData['timeout'] = $this->scopeConfig->getValue(self::TIMEOUT, $storeScope, $websiteCode);
        $configData['errores'] = $this->scopeConfig->getValue(self::ERRORES, $storeScope, $websiteCode);
        $configData['datos_iws'] = $this->scopeConfig->getValue(self::DATOS_TRAX, $storeScope, $websiteCode);
        $configData['datos_sales_iws'] = $this->scopeConfig->getValue(self::DATOS_SALES_TRAX, $storeScope, $websiteCode);
        $configData['datos_images_iws'] = $this->scopeConfig->getValue(self::DATOS_IMAGES_TRAX, $storeScope, $websiteCode);
        $configData['categorias_iws'] = $this->scopeConfig->getValue(self::DATOS_CATEGORIAS_TRAX, $storeScope, $websiteCode);
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

	public function getSkuList($category) 
	{
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
    public function beginCatalogLoad($configData, $storeId, $serviceUrl, $objectManager, $attempts) 
    {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl);
        if($data['status']){     
            $this->loadCatalogData($data['resp'], $objectManager, $storeId, $configData);
            $this->logger->info('GetProducts - Se actualiza información de todos los productos');
        } else {
            if(strpos((string)$configData['errores'], (string)$data['status_code']) !== false){
                if($configData['catalogo_reintentos']>$attempts){
                    $attempts++;
                    $this->logger->info('GetProducts - Error conexión: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexión. Se reintenta conexión #'.$attempts.' con el servicio.');
                    sleep($configData['timeout']);
                    $this->beginCatalogLoad($configData, $storeId, $serviceUrl, $objectManager, $attempts);
                } else{
                    $this->logger->info('GetProducts - Error conexión: '.$serviceUrl.' Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['catalogo_correo']);
                    $this->helper->notify('Soporte Trax', $configData['catalogo_correo'], $configData['catalogo_reintentos'], $serviceUrl, 'N/A', $storeId);
                }
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
        $this->logger->info('GetProducts- Service Url: '.$serviceUrl.' - status code: '.$status_code.' - curl errors: '.$curl_errors);
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

	public function loadCatalogData($data, $objectManager, $storeId, $configData) 
	{
        //Se carga objeto de productos
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        //Se recorre array
        foreach ($data as $key => $catalog) {
            //Se carga información de los productos
            $products = $productFactory->create();
            $product = $products->loadByAttribute('sku', $catalog->Sku);
            if($product){
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
                    $product->setName($name); // Name of Product        
                }   
                if($configData['product_description']){
                    $product->setDescription($description); // Description of Product      
                }
                $product->setAttributeSetId($configData['attribute_id']); // Attribute set id
                $product->setStatus(1); // Status on product enabled/ disabled 1/0
                $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                $product->setTaxClassId($configData['tax_id']); // Tax class id
                $product->setData('mpn',$catalog->Mpn); // Add Mpn
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
                if($configData['product_price']){
                    $product->setPrice($catalog->Price->UnitPrice);
                }
                if($configData['product_stock']){
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
                }
                try{
                    $product->save();
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
                    $product->save();
                    $this->logger->info('GetProducts - Se ha actualizado la información del producto con sku: '.$catalog->Sku);
                } catch (Exception $e){
                    $this->logger->info('GetProducts - Se ha actualizado la información del producto con sku: '.$catalog->Sku);
                }
            } else {
                $this->logger->info('GetProducts - No se encontro producto en magento asociado al sku: '.$catalog->Sku);
            }
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