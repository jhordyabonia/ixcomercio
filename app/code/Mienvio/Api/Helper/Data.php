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
        $status = array(
            'TRANSITO' => array(
                //@TODO: texto de estado de la orden: tránsito
                'msg' => __('Estado de la orden: tránsito'),
                'notify' => false,
				'newstatus' => false,
				//@TODO: texto de estado de la orden en el front: tránsito
				'frontlabel' => _('El pedido se encuentra en tránsito.')
            ),
            'ENTREGADO' => array(
                //@TODO: texto de estado de la orden: entregado
                'msg' => __('Estado de la orden: entregado'),
                'notify' => true,
				'newstatus' => 'complete',
				//@TODO: texto de estado de la orden en el front: entregado
				'frontlabel' => _('El pedido fue entregado.')
            ),
        );
        if(isset($status[$st])) return $status[$st];
        return array(
            //@TODO: texto de estado de la orden: desconocido
            'msg' => __('Estado desconocido'),
            'notify' => false,
            'newstatus' => false,
			//@TODO: texto de estado de la orden en el front: desconocido
			'frontlabel' => _('Error al obtener el estado del pedido, contacte al administrador.')
        );
    }
 
}