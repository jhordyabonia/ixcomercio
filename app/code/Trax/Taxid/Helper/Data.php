<?php
namespace Trax\Taxid\Helper;
 
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