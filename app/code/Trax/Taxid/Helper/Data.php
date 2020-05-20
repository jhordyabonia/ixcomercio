<?php
namespace Trax\Taxid\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper{
 
	public function __construct(){
		
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
 
}