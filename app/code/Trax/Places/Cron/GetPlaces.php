<?php
namespace Trax\Places\Cron;
use \Psr\Log\LoggerInterface;
use Trax\Places\Model\TraxPlacesRegionsFactory;
use Trax\Places\Model\TraxPlacesCitiesFactory;
use Trax\Places\Model\TraxPlacesLocalitiesFactory;

class GetPlaces {

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';

	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';

	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';

	const TIMEOUT = 'trax_general/catalogo_retailer/timeout';

	const ERRORES = 'trax_general/catalogo_retailer/errores';

    const LUGARES_REINTENTOS = 'trax_lugares/lugares_general/lugares_reintentos';

    const LUGARES_CORREO = 'trax_lugares/lugares_general/lugares_correo';

    const COUNTRY_ID = 'trax_lugares/lugares_general/country_id';
    
    private $helper;
	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    protected $logger;

    protected  $productRepository;     

    public function __construct(LoggerInterface $logger, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,     \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool, \Magento\Indexer\Model\IndexerFactory $indexerFactory,     \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory, 
    \Trax\Places\Model\TraxPlacesRegionsFactory $traxPlacesRegions,
    \Trax\Places\Model\TraxPlacesCitiesFactory $traxPlacesCities,
    \Trax\Places\Model\TraxPlacesLocalitiesFactory $traxPlacesLocalities,
    \Trax\Catalogo\Helper\Email $email) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/getplaces_cron.log');
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
        $this->_traxPlacesRegions = $traxPlacesRegions;
        $this->_traxPlacesCities = $traxPlacesCities;
        $this->_traxPlacesLocalities = $traxPlacesLocalities;
    }

/**
   * Write to system.log
   *
   * @return void
   */

    public function execute() 
    {
        $this->logger->info('GetPlaces - entra a cron ');
        //Se declaran variables de la tierra
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();


        $tableName = $resource->getTableName('trax_places_country'); 
		//Select Data from table
        $sql = "Select * FROM " . $tableName; 
        
        $this->logger->info('GetPlaces - sql country '.$sql);
        $country = $connection->fetchAll($sql); 

        $configData = $this->getConfigParams($storeScope, 'base');
        
        foreach ($country as $key => $data) {
                                  
            $this->logger->info('GetPlaces - country '.$data['country_code']);
            $configData['country_id'] = $data['country_code'];
            
            $serviceUrl = $this->getServiceUrl($configData, 'getplaces', false);   
            $this->logger->info('GetPlaces - url '.$serviceUrl);           

            if($data['country_code']== 'CR'){
                if($serviceUrl){
                    try{
                        $this->beginGetPlaces($configData, $serviceUrl, '', 0, 'region');
                    } catch(Exception $e){
                        $this->logger->info('GetPlaces - Se ha producido un error: '.$e->getMessage());
                    }
                    //TODO: Actualizar datos en base de datos con respuesta de IWS
                } else{
                    $this->logger->info('GetPlaces - Se ha producido un error al conectarse al servicio. No se detectaron parametros de configuracion');
                }
            }
            
        }
    }

    //Obtiene los parámetros de configuración desde el cms
    public function getConfigParams($storeScope, $websiteCode = null) 
    {
        //Se obtienen parametros de configuración por Store
        $configData['apikey'] = $this->scopeConfig->getValue(self::API_KEY, $storeScope);
        $configData['accesskey'] = $this->scopeConfig->getValue(self::ACCESS_KEY, $storeScope);
        $enviroment = $this->scopeConfig->getValue(self::ENVIROMENT, $storeScope);
        //Se valida entorno para obtener url del servicio
        if($enviroment == '0'){
            $configData['url'] = $this->scopeConfig->getValue(self::URL_DESARROLLO, $storeScope);
        } else{
            $configData['url'] = $this->scopeConfig->getValue(self::URL_PRODUCCION, $storeScope);
        }
        $configData['timeout'] = $this->scopeConfig->getValue(self::TIMEOUT, $storeScope);
        $configData['errores'] = $this->scopeConfig->getValue(self::ERRORES, $storeScope);
        $configData['lugares_reintentos'] = $this->scopeConfig->getValue(self::LUGARES_REINTENTOS, $storeScope);
        $configData['lugares_correo'] = $this->scopeConfig->getValue(self::LUGARES_CORREO, $storeScope);
        
        return $configData;
    }

    /*Genera la url de consumo del servicio
    * Si $type = 1 se obtiene la información general del catalogo
    * Si $type != 1 se obtiene el precio e inventario del catalogo
    */
	public function getServiceUrl($configData, $method, $parentId) 
	{
        if($configData['apikey'] == ''){
            $serviceUrl = false;
        } else {
            $utcTime = gmdate("Y-m-d").'T'.gmdate("H:i:s").'Z';          
            $signature = $configData['apikey'].','.$configData['accesskey'].','.$utcTime;
            $signature = hash('sha256', $signature);
            //$api_key = '63849496-b505-485f-abb5-5c4b15a9a0d4';
            //$signature = '3d8b4a7f494930dec2415baf5af9ddce8b0701b65c68323c228b288a56f66671';
            $serviceUrl = $configData['url'].$method.'?locale=en&apiKey='.$configData['apikey'].'&utcTimeStamp='.$utcTime.'&signature='.$signature.'&countryId='.$configData['country_id']; 
        }
        if($parentId){
            $serviceUrl .= '&parentId='.$parentId;
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
        $this->logger->info('GetPlaces - status code: '.$status_code);
        $this->logger->info('GetPlaces - '.$serviceUrl);
        $this->logger->info('GetPlaces - curl errors: '.$curl_errors);
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

    //Función recursiva para intentos de conexión
    public function beginGetPlaces($configData, $serviceUrl, $storeCode, $attempts, $type, $parent_id = null) {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl);
        if($data['status']){
            if(count($data['resp'])>0){
                $this->logger->info('GetPlaces - Se obtuvo respuesta del servicio');
                //Mapear lugares de magento con IWS en tabla custom
                $this->loadPlaces($configData, $storeCode, $data['resp'], $type, $parent_id);
            } else{
                $this->logger->info('GetPlaces - El servicio no retorna información');
            }
        } else {
            if(strpos((string)$configData['errores'], (string)$data['status_code']) !== false){
                if($configData['lugares_reintentos']>$attempts){
                    $attempts++;
                    $this->logger->info('GetPlaces - Error conexión: '.$serviceUrl.' Se esperan '.$configData['timeout'].' segundos para reintento de conexión. Se reintenta conexión #'.$attempts.' con el servicio.');
                    sleep($configData['timeout']);
                    $this->beginGetPlaces($configData, $serviceUrl, $storeCode, $attempts, $type, $parent_id);
                } else{
                    $this->logger->info('GetPlaces - Error conexión: '.$serviceUrl);
                    $this->logger->info('GetPlaces - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$configData['lugares_correo']);
                    $this->helper->notify('Soporte Trax', $configData['lugares_correo'], $configData['lugares_reintentos'], $serviceUrl, "N/A", $storeCode);
                    echo json_encode(array());
                }
            } else{
                $this->logger->info('GetPlaces - No se establecio conexión con el servicio');
            }
        }   

    }

    //Función que carga las regiones asociadas a un pais y una tienda en especifico
    public function loadPlaces($configData, $storeCode, $data, $type, $parent_id = null) {
        //Se leen datos de la respuesta
        $places = array();
        foreach ($data as $key => $region) {
            $this->logger->info('GetPlaces - Se verifica si el registro de '.$type.' con id de trax: '.$region->Id.' existe');
            $id = $this->checkPlace($configData['country_id'], $storeCode, $region->Id, $type);
            //Se verifica si existe el registro para el pais y la tienda

            $this->logger->info('GetPlaces - Se verifica id '.$id.' con store: '.$storeCode.' existe');
            
            if(!$id){
                //Se genera registro
                $this->savePlace($configData['country_id'], $storeCode, $region, $type);
            } else {
                //Se actualiza información
                $this->updatePlace($id, $type, $region);
            }
            $places[$region->Id] = $region->Id;
            //Se cargan las places hijos
            switch($type){
                case 'region': $type2 = 'city'; break;
                case 'city': $type2 = 'locality'; break;
                case 'locality': $type2 = $type; break;
            }
            $serviceUrl = $this->getServiceUrl($configData, 'getplaces', $region->Id);
            $this->beginGetPlaces($configData, $serviceUrl, $storeCode, 0, $type2, $id);
            //Se verifican los registros que no cumplan para dejarlos con estado 0
        }
        if(count($places)>0){
            $this->checkPlaces($places, $configData, $storeCode, $type);
        }
    }

    //Consulta la tabla custom de places y verifica si la region existe
    public function checkPlace($country_id, $storeCode, $place_id, $type) 
    {   
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        switch($type){
            case 'region':
                $table = 'trax_places_regions'; break;
            case 'city':
                $table = 'trax_places_cities'; break;
            case 'locality':
                $table = 'trax_places_localities'; break;
        }
		$tableName = $resource->getTableName($table); 
		//Select Data from table
        $sql = "Select * FROM " . $tableName." where country_id='".$country_id."' AND trax_id='".$place_id."'";
       
        $place = $connection->fetchAll($sql); 
        foreach ($place as $key => $data) {
            return $data['id'];
        }
        return false;
    }

    //Guarda un place
    public function savePlace($country_id, $storeCode, $region, $type, $parent = null) 
    {   
        switch($type){
            case 'region':
                $model = $this->_traxPlacesRegions->create(); 
                $model->addData([                    
                    "country_id" => $country_id,
                    "trax_id" => $region->Id,
                    "name" => $region->Name,
                    "level" => $region->Level,
                    "parent_id" => $region->ParentId,
                    "area_code" => $region->AreaCode,
                    "postal_code" => $region->PostalCode
                    ]);
                break;
            case 'city':
                $model = $this->_traxPlacesCities->create(); 
                $model->addData([                    
                    "country_id" => $country_id,
                    "trax_places_region_id" => $parent,
                    "trax_id" => $region->Id,
                    "name" => $region->Name,
                    "level" => $region->Level,
                    "parent_id" => $region->ParentId,
                    "area_code" => $region->AreaCode,
                    "postal_code" => $region->PostalCode
                    ]);
                break;
            case 'locality':
                $model = $this->_traxPlacesLocalities->create(); 
                $model->addData([                    
                    "country_id" => $country_id,
                    "trax_places_city_id" => $parent,
                    "trax_id" => $region->Id,
                    "name" => $region->Name,
                    "level" => $region->Level,
                    "parent_id" => $region->ParentId,
                    "area_code" => $region->AreaCode,
                    "postal_code" => $region->PostalCode
                    ]);
                break;
        }		
        $saveData = $model->save();
        if($saveData){
            $this->logger->info('GetPlaces - Se inserto el place de tipo '.$type.' con id de trax: '.$region->Id);
        } else {
            $this->logger->info('GetPlaces - Se produjo un error al guardar el place de tipo '.$type.' con id de trax: '.$region->Id);
        }
    }

    //Actualiza un place
    public function updatePlace($id, $type, $region) 
    {   
        switch($type){
            case 'region':
                $model = $this->_traxPlacesRegions->create(); break;
            case 'city':
                $model = $this->_traxPlacesCities->create(); break;
            case 'locality':
                $model = $this->_traxPlacesLocalities->create(); break;
        }		
        $model->setData([
            "id" => $id,
            "trax_id" => $region->Id,
            "name" => $region->Name,
            "level" => $region->Level,
            "parent_id" => $region->ParentId,
            "area_code" => $region->AreaCode,
            "postal_code" => $region->PostalCode,
            "status" => 1
            ]);
        $saveData = $model->save();
        if($saveData){
            $this->logger->info('GetPlaces - Se actualizo el place de tipo '.$type.' con id de trax: '.$id);
        } else {
            $this->logger->info('GetPlaces - Se produjo un error al actualizar el place de tipo '.$type.' con id de trax: '.$id);
        }
    }

    //Consulta la tabla custom de places y verifica si la region existe
    public function checkPlaces($places, $configData, $storeCode, $type) 
    {   
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        switch($type){
            case 'region':
                $table = 'trax_places_regions'; break;
            case 'city':
                $table = 'trax_places_cities'; break;
            case 'locality':
                $table = 'trax_places_localities'; break;
        }
		$tableName = $resource->getTableName($table); 
		//Select Data from table
        $sql = "Select * FROM " . $tableName." where store_code='".$storeCode."' AND country_id='".$configData['country_id']."'";
        $place = $connection->fetchAll($sql); 
        foreach ($place as $key => $data) {
            if(!array_key_exists ( $data['trax_id'] , $places )){
                $this->disablePlace($data['id'], $data['trax_id'], $type);
            }
        }
    }

    //Actualiza un place
    public function disablePlace($id, $trax_id, $type) 
    {   
        switch($type){
            case 'region':
                $model = $this->_traxPlacesRegions->create(); break;
            case 'city':
                $model = $this->_traxPlacesCities->create(); break;
            case 'locality':
                $model = $this->_traxPlacesLocalities->create(); break;
        }		
        $model->setData([
            "id" => $id,
            "status" => 0
            ]);
        $saveData = $model->save();
        if($saveData){
            $this->logger->info('GetPlaces - Se deshabilito el place de tipo '.$type.' con id de trax: '.$trax_id);
        } else {
            $this->logger->info('GetPlaces - Se produjo un error al deshabilitar el place de tipo '.$type.' con id de trax: '.$id);
        }
    }

}