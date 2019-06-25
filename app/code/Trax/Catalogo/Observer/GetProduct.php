<?php

namespace Trax\Catalogo\Observer;
use \Psr\Log\LoggerInterface;

class GetProduct implements \Magento\Framework\Event\ObserverInterface
{

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_catalogo/catalogo_general/apuntar_a';

	const URL_DESARROLLO = 'trax_catalogo/catalogo_general/url_desarrollo';

	const URL_PRODUCCION = 'trax_catalogo/catalogo_general/url_produccion';

    const DATOS_TRAX = 'trax_catalogo/catalogo_general/datos_iws';

    const DATOS_SALES_TRAX = 'trax_catalogo/catalogo_general/datos_sales_iws';

    const DATOS_IMAGES_TRAX = 'trax_catalogo/catalogo_general/datos_images_iws';

    const DATOS_PRODUCTOS_TRAX = 'trax_catalogo/catalogo_general/productos_iws';
	
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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
	}
	
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		if(isset($_GET["variable"]) && $_GET["variable"]=='show'){
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
			//Se obtienen parametros de configuración por Store
			$configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode());
			//Se obtiene lista de sku
			$sku = $this->getSku($observer->getEvent());
			//Se obtiene url del servicio
			$serviceUrl = $this->getServiceUrl($configData, $sku);
            //Se carga el servicio por curl
            if($configData['datos_iws']){
                $data = $this->loadIwsService($serviceUrl);
                if($data){      
					$this->loadProductsData($data, $objectManager, $storeManager->getStore()->getStoreId());
                    $this->logger->info('GetProduct - Se actualiza información de todos los productos');
                } else {
                    $this->logger->info('GetProduct - Error conexión: '.$serviceUrl);
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
        return $configData;

    }

	public function getSku($event) 
	{
		$product = $event->getData('product');
        return $product->getSku();
    }

	public function getServiceUrl($configData, $sku) 
	{
        if($configData['apikey'] == ''){
            $serviceUrl = false;
        } else {
            $utcTime = gmdate("Y-m-d").'T'.gmdate("H:i:s").'Z';
            $signature = $configData['apikey'].','.$configData['accesskey'].','.$utcTime;
            $signature = hash('sha256', $signature);
            $serviceUrl = $configData['url'].'getproduct?locale=en&apiKey='.$configData['apikey'].'&utcTimeStamp='.$utcTime.'&signature='.$signature.'&sku='.$sku.'&includePriceData=true&includeInventoryData=true'; 
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
			$this->logger->info('GetProduct - Se ha actualizado la información del producto con sku: '.$catalog->Sku);
        } else {
			$this->logger->info('GetProduct - No se encontro producto en magento asociado al sku: '.$catalog->Sku);
		}
    }
}