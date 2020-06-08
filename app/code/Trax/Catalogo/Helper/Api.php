<?php
namespace Trax\Catalogo\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;

class Api extends AbstractHelper{
	
	const API_KEY = 'trax_general/catalogo_retailer/apikey';
	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';
	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';
	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';
	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';
	const TIMEOUT = 'trax_general/catalogo_retailer/timeout';
	const ERRORES = 'trax_general/catalogo_retailer/errores';

	public function __construct(){

	}

	public function getConfigFields($type = null){
        $data = array(
            'apikey' => self::API_KEY,
            'accesskey' => self::ACCESS_KEY,
            'enviroment' => self::ENVIROMENT,
            'url_stagging' => self::URL_DESARROLLO,
            'url_prod' => self::URL_PRODUCCION,
            'timeout' => self::TIMEOUT,
            'errores' => self::ERRORES,
        );
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