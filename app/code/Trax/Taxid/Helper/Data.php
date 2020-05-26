<?php
namespace Trax\Taxid\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper{
	
	const API_KEY = 'trax_general/catalogo_retailer/apikey';
	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';
	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';
	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';
	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';
	const TIMEOUT = 'trax_general/catalogo_retailer/timeout';
	const ERRORES = 'trax_general/catalogo_retailer/errores';

	public function __construct(){

	}

	public function getValidCalls($type = null){
        $data = array(
            'invoice.upload' => array(
                'ws_config' => array(
                    'apikey' => self::API_KEY,
                    'accesskey' => self::ACCESS_KEY,
                    'enviroment' => self::ENVIROMENT,
                    'url_stagging' => self::URL_DESARROLLO,
                    'url_prod' => self::URL_PRODUCCION,
                    'timeout' => self::TIMEOUT,
                    'errores' => self::ERRORES,
                ) 
            )
        );
        if($type && isset($data[$type])) return $data[$type];
        return $data;
    }

	public function getCommentByStatus($st){
		$trakStr = '';
		if(isset($st['label_url'])){
			$trakStr = sprintf(
				__(
					"Tu número de guía es N° %s\n\nVerifica el estado de tu pedido aquí:\n%s\n\nVerifica tu guía aquí:\n%s"
				),
				$st['tracking_number'],
				$st['tracking_url'],
				$st['label_url']
			);
		}
        $status = array(
			'LABEL_CREATED' => array(
                //@TODO: texto de estado de la orden: tránsito
                'msg' => sprintf(
					__("¡Hemos generado la guía de tu pedido!\n\n%s"),
					$trakStr
				),
                'notify' => true,
				'newstatus' => false,
				'frontlabel' => __('¡Hemos generado la guía de tu pedido!')
            ),
			'TRANSITO' => array(
                //@TODO: texto de estado de la orden: tránsito
                'msg' => sprintf(
					__("¡Tu paquete está por llegar!\n\n%s"),
					$trakStr
				),
                'notify' => true,
				'newstatus' => false,
				'frontlabel' => __('¡Tu paquete está por llegar!')
            ),
            'ENTREGADO' => array(
                //@TODO: texto de estado de la orden: entregado
                'msg' => sprintf(
					__("¡Tu paquete ha sido entregado!\n\n%s"),
					$trakStr
				),
                'notify' => true,
				'newstatus' => 'complete',
				'frontlabel' => __('¡Tu paquete ha sido entregado!')
            ),
        );
        if(isset($st['status'], $status[$st['status']])) return $status[$st['status']];
        return array(
            //@TODO: texto de estado de la orden: desconocido
            'msg' => __('Estado desconocido'),
            'notify' => false,
            'newstatus' => false,
			'frontlabel' => __('Error al obtener el estado del pedido, contacte al administrador.')
        );
	}
	
	/*Configura el header para la consulta del WS*/
	public function getOutcommingHeader($configData){
		return array(
            'Content-Type: application/json',
			'Authorization: Bearer '.$configData['token']
		);
	}
 
}