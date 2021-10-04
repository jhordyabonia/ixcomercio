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
        'noMienvio' => [], //Órdenes que no tienen id de mienvio
        'noTraxWS' => [], //Órdenes que no se encuentran en trax al consultar el WS
        'noMienvioWs' => [] //Órdenes que tienen id de mienvio, pero no se encuentran al consultar el WS
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
        $this->_dump = false;
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
        //Cancel fail orders
        $this->cancelFailOrders();
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
            $this->failOrders['noIws'][$order->getIncrementId()] = $order;
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
                $this->failOrders['noMienvio'][$order->getIncrementId()] = $order;
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
            $this->failOrders['noMienvioWs'][$order->getIncrementId()] = $order;
            $this->log('Error en mi envío al actualizar la orden con id iws: '.$iwsOrder->getId());
        }
    }

    /**
     * Consula el estado de la orden en IWS getOrder
     */
    function getOrderInfo($order, $iwsOrder){
        $this->log('Inicia consulta del WS getOrder de trax');
        try{
            $data = $this->getWSData($iwsOrder, $order);
            //si no tiene info de factura, genera y guarda
            $comment = $this->_cdiHelper->getCommentByStatus($data, 'invoice_curl');
            if(is_array($comment)){
                $this->_cdiHelper->addOrderComment(
                    $order, 
                    $comment['msg'],
                    $comment['notify'],
                    $comment['newstatus']
                );
                $this->log('Se actualiza el estado de la orden a ' . $comment['newstatus']);
            }else{
                $this->log('no se realizan cambios sobre la orden');
            }
        } catch (\Exception $e) {
            $this->failOrders['noTraxWS'][$order->getIncrementId()] = $order;
            $this->log('Error al actualizar la orden con id: '.$iwsOrder->getId());
            throw $e;
        }
    }

        //Obtiene la información del WS
    private function getWSData($iwsOrder, $order){
        $orderStore = $order->getStore();
        //Parámetros de configuración del WS
        $configKeys = $this->_ordersHelper->getConfigFields();
        $configData = $this->_cdiHelper->getConfigParams($configKeys, $orderStore->getCode());
        if(!$configData['enviroment']){
            $configData['url'] = $configData['url_stagging'];
        }else{
            $configData['url'] = $configData['url_prod'];
        }
        unset($configData['url_stagging'], $configData['url_prod']);
        //Consume el WS
        $trax_data_array = array();
        $trax_data = $this->loadTraxData($configData, $order->getIncrementId());
        $this->log(print_r($trax_data,true));
        if(!empty($trax_data['resp'])){
            $obj = $trax_data['resp'];

            $trax_data_array['status'] = $obj->Description;
            $trax_data_array['statusCode'] = $obj->StatusCode;
        }
        /*if(isset($trax_data['status']) && $trax_data['status']){
            $obj = $trax_data['resp'];

            $trax_data_array['status'] = $obj->Status->Description;
            $trax_data_array['statusCode'] = $obj->Status->StatusCode;
        }*/
        $this->log('Información recibida del WS');
        $this->log(print_r($trax_data_array, true));
        if(empty($trax_data_array)) throw new \Exception("No se pudo obtener información del WS de factura");
        return $trax_data_array;
    }

    /**
     * Realiza el consumo del WS
     */
    public function loadTraxData($configData, $iwsOrderId){
        $params = array('ordernumber' => $iwsOrderId);
        $wsdl = $this->_cdiHelper->prepateTraxUrl('getorderstatus', $configData, $params, $this->logger);
        $try = 0; $sleep = 3;
        return $this->_cdiHelper->makeCurl($wsdl, false, $this->logger, $try, $sleep);
    }

    /**
     * Realiza consulta del WS para la orden y verifica su estado
     */
    private function processOrder($order){
        $this->log('Se consulta con datos del Store ' . $order->getStore()->getCode());
        //IWS
        $iwsOrder = $this->getIwsOrder($order);
        //Mienvío
        $this->processMienvio($order, $iwsOrder);
        //Obtiene la información de trax del método getOrder
        $this->getOrderInfo($order, $iwsOrder);
    }

    /**
     * Cancela las órdenes que no se registraron en IWS
     */
    private function cancelFailOrders(){
        if(count($this->failOrders['noiws'])){
            $this->log('Órdenes que no se encuentran en la tabla IWS');
            $this->log(print_r(array_keys($this->failOrders['noiws']), true));
        }
        if(count($this->failOrders['noMienvio'])){
            $this->log('Órdenes que no tienen id de mienvio');
            $this->log(print_r(array_keys($this->failOrders['noMienvio']), true));
        }
        if(count($this->failOrders['noTraxWS'])){
            $this->log('Órdenes que no se encuentran en trax al consultar el WS');
            $this->log(print_r(array_keys($this->failOrders['noTraxWS']), true));
        }
        if(count($this->failOrders['noMienvioWs'])){
            $this->log('Órdenes que tienen id de mienvio, pero no se encuentran al consultar el WS');
            $this->log(print_r(array_keys($this->failOrders['noMienvioWs']), true));
        }

        foreach($this->failOrders as $type => $orders){
            //Mensaje de cancelación personalizado
            switch($type){
                //case 'noIws':
                    //$msg = 'La orden no tiene id de IWS en la tabla iws_orders';
                    //break;
                //case 'noTraxWS':
                    //$msg = 'La orden cuenta con id de IWS en la tabla iws_orders, pero no se encuentra al consultar el WS.';
                    //break;
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
                'val' => ['closed', 'complete', 'canceled','pending']
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