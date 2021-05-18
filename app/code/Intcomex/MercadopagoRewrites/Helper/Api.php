<?php


use Magento\Store\Model\ScopeInterface;

namespace Intcomex\MercadopagoRewrites\Helper;

/**
 * Class Api
 * @package Intcomex\MercadopagoRewrites\Helper
 */
class Api
{
    const USER_API = 'payment/mercadopago/username_api';

    const PASSWORD_API = 'payment/mercadopago/password_api';


    /**
     * @var \Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    


    public function  __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        
    ){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/apiMercadopago.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $this->_scopeConfig = $scopeConfig; 
    }



    public function getAccesTokenApi(){

        
        $storeScope= \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $this->logger->info('Api Mercadopago - storeScope ' . $storeScope);

        $configData['user_api'] = $this->_scopeConfig->getValue(self::USER_API, $storeScope);   
        $configData['password_api'] = $this->_scopeConfig->getValue(self::PASSWORD_API, $storeScope);   
        
        $this->logger->info('Api Mercadopago - user_api ' . $configData['user_api']);
        $this->logger->info('Api Mercadopago - password_api ' . $configData['password_api']);


        $url = 'https://'.$_SERVER['SERVER_NAME'].'/index.php/rest/V1/integration/admin/token';

        $this->logger->info('Api Mercadopago - url api ' . $url);
 
        $data = array("username" => 'mercadopago', "password" => 'cF3TKmQ5V8mH');
        $data_string = json_encode($data);
        
        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)
        );
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $token = curl_exec($ch);

        $this->logger->info('Api Mercadopago - result api ' . $token);
        $result_api = json_decode($token,true);
        
        if(isset($result_api['message'])){            
            $this->logger->info('Api Mercadopago - Error api ' . $result_api['message']);
            return false;
        }else{
            $token = json_decode($token);
        }

        return $token;

    }



    public function getOrdenByIncrementId($idOrden)
    {

        $token = $this->getAccesTokenApi();

        $requestUrl = "https://".$_SERVER['SERVER_NAME']."/rest/V1/orders?searchCriteria[filter_groups][0][filters][0][field]=increment_id&searchCriteria[filter_groups][0][filters][0][value]=".$idOrden."&searchCriteria[filter_groups][0][filters][0][condition_type]=eq";

        $headers = array("Authorization: Bearer $token");
 
        $ch = curl_init($requestUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        
        $result=  json_decode($result,true);


        $object = json_decode(json_encode($result["items"][0]), FALSE);
        
        //echo $object->getbase_currency_code;

        $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        

        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($idOrden);

        

        return $order;


    }
}