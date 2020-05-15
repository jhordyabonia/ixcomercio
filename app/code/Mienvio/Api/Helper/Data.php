<?php
namespace Mienvio\Api\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data extends AbstractHelper{
 
	protected $pageFactory;
	protected $_scopeConfig;
	protected $_storeManager;
    /**
     * @var TimezoneInterface
     */
	protected $localeDate;
	
	public function __construct(
		PageFactory $pageFactory, 
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		TimezoneInterface $localeDate,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	){
		$this->pageFactory = $pageFactory;
		$this->_scopeConfig = $scopeConfig;
		$this->localeDate = $localeDate;
		$this->_storeManager = $storeManager;	
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