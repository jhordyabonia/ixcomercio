<?php
namespace Trax\Catalogo\Cron;

use \Psr\Log\LoggerInterface;
use Cdi\Custom\Helper\Api as CdiApi;
use Trax\Catalogo\Helper\Api as TraxApi;
use Mienvio\Api\Controller\Shipment\Api;

class OrderStatus {
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $_orderCollectionFactory;
    protected $logger;
    protected $_cdiHelper;
    protected $_mienvioApi;
    protected $_ordersHelper;
    protected $_dump;

    private $failOrders = [
        'noiws' => [], //Órdenes que no se encuentran en la tabla IWS
        'noMienvio' => [] //Órdenes que no tienen id de mienvio
    ];

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Cdi\Custom\Helper\Api $cdiHelper,
        \Mienvio\Api\Controller\Shipment\Api $mienvioApi,
        \Trax\Catalogo\Helper\Api $ordersHelper
    ) {
        //Define el log
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cronOrderStatus.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        //Params
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_cdiHelper = $cdiHelper;
        $this->_mienvioApi = $mienvioApi;
        $this->_ordersHelper = $ordersHelper;
        $this->_dump = true;
    }

/**
   * Write to system.log
   *
   * @return void
   */

    /**
     * Genera dump para depuración web
     */
    private function dump($obj, $die = false, $title = null){
        if($this->_dump) $this->_cdiHelper->dump($obj, $die, $title);
    }

    /**
     * Agrega registros al log
     */
    private function log($str){
        $this->logger->info($str);
        if($this->_dump) echo "{$str}<br/>";
    }

    public function execute() 
    {
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        //Se obtiene los filtros desde una función por si más adelante es necesario agremar más
        $filters = $this->getFilters();
        //Se declaran variables de la 
        $this->log('Inicia Cron de órdenes');
        $orders = $this->getOrderCollectionByFilter($filters);
        foreach($orders as $order){
            $this->log('-----------------------');
            $id = $order->getIncrementId();
            $status = $order->getStatus();
            try{
                $this->log("Orden: {$id} con estado {$status}");
                $this->processOrder($order);
            }catch(\Exception $e){
                $this->log($e->getMessage());
            }
        }
        $this->log('Finaliza Cron de órdenes');
    }

    /**
     * Retorna la orden de la tabla iws_orders
     */
    private function getIwsOrder($order){
        try{
            $this->log("Busca la orden en la tabla de ordenes IWS");
            $iwsOrder = $this->_cdiHelper->getIwsOrderBy('order_id', $order->getEntityId(), $this->logger);
            $this->log("La orden {$order->getEntityId()} tiene el id IWS #{$iwsOrder['iws_order']}");
            return $iwsOrder;
        }catch(\Exception $e){
            $this->failOrders['noIws'][$order->getEntityId()] = $order;
            throw $e;
        }
    }

    /**
     * Resaliza actualización de estado de mienvio
     */
    private function processMienvio($order, $iwsOrder){
        try{
            $this->log("Verifica estado en mienvio");
            $mienvioQuoteId = (int) $order->getMienvioQuoteId();
            if(!$mienvioQuoteId){
                $this->failOrders['noMienvio'][$order->getEntityId()] = $order;
                $this->log("La orden no tiene id de mienvio, no se consulta.");
                return;
            }
            $this->log("Mienvio Quoteid {$mienvioQuoteId}");
            list($updated, $newStatus) = $this->_mienvioApi->saveMienvioData(
                'shipment.status', 
                $order, 
                $iwsOrder, 
                $this->logger
            );
            if(!$updated){
                $this->log("No se encontró información actualizada de mienvio");
            }else{
                $this->log("Se encontró actualización en mienvio. Nuevo estado: {$newStatus}");
            }
        }catch(\Exception $e){
            $this->log('Error en mi envío al actualizar la orden con id iws: '.$iwsOrder->getId());
        }
    }

    /**
     * Consula el estado de la orden en IWS getOrder
     */
    function getOrderInfo($order, $iwsOrder){
        try{
            $data = $this->getWSData($iwsOrder);
            die('tester');
            //si no tiene info de factura, genera y guarda
            if(!$iwsOrder->getTraxInvoice() || $this->_simulate['validate']){
                $comment = $this->_cdiHelper->getCommentByStatus($data, 'invoice');
                $this->_cdiHelper->addOrderComment(
                    $order, 
                    $comment['msg'],
                    $comment['notify'],
                    $comment['newstatus']
                );
                $iwsOrder->setTraxInvoice(serialize($data));
                $iwsOrder->save();
                $this->logger->info('Mienviowebhook - Se actualizo la orden : '.$iwsOrder->getId());
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->info('Mienviowebhook - Error al actualizar la orden con id: '.$iwsOrder->getId());
            throw $e;
        }
    }

        //Obtiene la información del WS
    private function getWSData($iwsOrder){
        //Parámetros de configuración del WS
        $configKeys = $this->_ordersHelper->getConfigFields($type);
        $this->dump($configKeys, true);
        $configData = $this->_cdiHelper->getConfigParams($configKeys['ws_config']);
        if(!$configData['enviroment']){
            $configData['url'] = $configData['url_stagging'];
        }else{
            $configData['url'] = $configData['url_prod'];
        }
        unset($configData['url_stagging'], $configData['url_prod']);
        //Consume el WS
        $trax_data_array = array();
        $trax_data = $this->loadTraxData($configData, $iwsOrder->getIwsOrder());
        if(isset($trax_data['status']) && $trax_data['status']){
            $obj = $trax_data['resp'];
            $trax_data_array['status'] = 'INVOICE_CREATED';
            $trax_data_array['InvoiceNumber'] = $obj->InvoiceNumber;
            $trax_data_array['InvoiceDate'] = $obj->InvoiceDate;
            $trax_data_array['TaxRegistrationNumber'] = $obj->TaxRegistrationNumber;
            $trax_data_array['InvoiceUrl'] = $obj->InvoiceUrl;
        }
        $this->logger->info('Información recibida del WS');
        $this->logger->info(print_r($trax_data_array, true));
        if(empty($trax_data_array)) throw new \Exception("No se pudo obtener información del WS de factura");
        return $trax_data_array;
    }

    /**
     * Realiza consulta del WS para la orden y verifica su estado
     */
    private function processOrder($order){
        //IWS
        $iwsOrder = $this->getIwsOrder($order);
        //Mienvío
        $this->processMienvio($order, $iwsOrder);
        //Obtiene la información de trax del método getOrder
        $this->getOrderInfo($order, $iwsOrder);
        //Cancel fail orders
        $this->cancelFailOrders();
    }

    /**
     * Cancela las órdenes que no se registraron en IWS
     */
    private function cancelFailOrders(){
        foreach($this->failOrders as $type => $orders){
            //Mensaje de cancelación personalizado
            switch($type){
                case 'noIws':
                    $msg = 'No se registró la orden en IWS';
                    break;
                default:
                    $msg = false;
            }
            foreach($orders as $order){
                if(!$msg) continue;
                $this->_cdiHelper->addOrderComment(
                    $order, 
                    $msg,
                    false,
                    'canceled'
                );
            }
        }
    }

    /**
     * HELPER
    */
    /*
    * Retorna el listado de filtros para la búsqueda de la órdenes
    */
    private function getFilters()
    {
        return [
            //Ordenes con estado diferente a completado o cerrado
            [
                'field' => 'status',
                'type' => 'nin',
                'val' => ['closed', 'complete', 'canceled']
            ]
        ];
    }

    /*
    * Retorna el órdenes, recibe como parámetro un array con filtros
    */
    private function getOrderCollectionByFilter($filters = [])
    {
        $collection = $this->_orderCollectionFactory->create()->addFieldToSelect('*');
        foreach($filters as $filter){
            $collection->addFieldToFilter(
                $filter['field'],
                [$filter['type'] => $filter['val']]
            );
        }
        return $collection;
    }
}