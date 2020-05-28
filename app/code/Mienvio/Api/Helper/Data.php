<?php
namespace Mienvio\Api\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper{
	
	const USER = 'shipping/mienvio_api/user';    
    const PASSWORD = 'shipping/mienvio_api/password';
    const TOKEN = 'carriers/mienviocarrier/apikey';
	const ENVIROMENT = 'shipping/mienvio_api/apuntar_a';
	const URL_STAGING = 'shipping/mienvio_api/url_staging';
	const URL_PRODUCCION = 'shipping/mienvio_api/url_produccion';

	public function __construct(){

	}

	public function getValidCalls($type = null){
        $data = array(
            'shipment.status' => array(
                'ws_config' => array(
                    'user' => self::USER,
                    'password' => self::PASSWORD,
                    'token' => self::TOKEN,
                    'enviroment' => self::ENVIROMENT,
                    'url_stagging' => self::URL_STAGING,
                    'url_prod' => self::URL_PRODUCCION
                ) 
            ),
            'shipment.upload' => array(
                'ws_config' => array(
                    'user' => self::USER,
                    'password' => self::PASSWORD,
                    'token' => self::TOKEN,
                    'enviroment' => self::ENVIROMENT,
                    'url_stagging' => self::URL_STAGING,
                    'url_prod' => self::URL_PRODUCCION
                )
            ),
        );
        if($type && isset($data[$type])) return $data[$type];
        return $data;
    }

	/*Configura el header para la consulta del WS*/
	public function getOutcommingHeader($configData){
		return array(
            'Content-Type: application/json',
			'Authorization: Bearer '.$configData['token']
		);
	}
 
}