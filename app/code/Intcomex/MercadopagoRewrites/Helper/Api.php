<?php

namespace Intcomex\MercadopagoRewrites\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order;
use \Psr\Log\LoggerInterface;
use \Intcomex\EventsObservers\Helper\RegisterPayment;

/**
 * Class Api
 * @package Intcomex\MercadopagoRewrites\Helper
 */
class Api
{
    /**
     * @var \Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;   
    
    /**
    * @var EventManager
    */
    private $eventManager;

    /**
    * @var helper
    */
    protected $helperRegister;


    public function  __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        \Magento\Framework\Event\Manager $eventManager,
        RegisterPayment $helper
        
    ){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/apiMercadopago.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);


        $writer_event = new \Zend\Log\Writer\Stream(BP . '/var/log/eventMPSucces.log');
        $this->logger_event = new \Zend\Log\Logger();
        $this->logger_event->addWriter($writer_event);

        $this->eventManager = $eventManager;

        $this->_scopeConfig = $scopeConfig; 

        $this->helperRegister = $helper;
    }


    public function getOrdenByIncrementId($idOrden, $reintentos = 0)
    {
        $this->logger->info('Mercadopago Helper - orden '.$idOrden);
        $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        $this->logger->info('Mercadopago Helper - reintentos '.$reintentos);
        
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($idOrden);

        return $order;

    }

    /*
    * Set status order canceled
    *
    */

    public function setOrdenStatusCanceled($idOrden)
    {
       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('\Magento\Sales\Model\Order')->loadByIncrementId($idOrden);
        $orderState = Order::STATE_CANCELED;
        $order->setState($orderState)->setStatus(Order::STATE_CANCELED);
        $order->save();

        $this->logger->info('Mercadopago Helper - orden '.$idOrden);
        $this->logger->info('Mercadopago Helper - orden status '.$order->getState());

        return $order;

    }



    public function eventCheckoutSucess($order)
    {
        $this->eventManager->dispatch('checkout_onepage_controller_success_action', ['order'=>$order]);
        $this->logger_event->info('Mercadopago Helper - event checkout_onepage_controller_success_action');
    }


    public function getRegysterPayment($order)
    {
        $storeScope    = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();     
        $storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $payment = $order->getPayment();

        $this->logger_event->info('Consultamos el payload');
        $payload = $this->helperRegister->loadPayloadService(
                    $order->getId(), 
                    $payment->getAmountOrdered(), 
                    '1234567',
                    (!empty($payment->getLastTransId()))?$payment->getLastTransId():'1234567', 
                    '', 
                    $payment->getMethod(), 
                    $storeManager->getWebsite($storeManager->getStore($order->getStoreId())->getWebsiteId())->getCode()
            );
        $this->logger_event->info('RegisterPayment - PayLoad '. $payload);

        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('iws_order'); 
        //Select Data from table
        $sql = "Select * FROM " . $tableName." where order_increment_id='".$order->getIncrementId()."'";

        $this->logger_event->info('RegisterPayment - iws_order sql '.$sql);

        $trax = $connection->fetchAll($sql); 

        $this->logger_event->info('RegisterPayment - iws_order result '.json_encode($trax));

        $mp_order = 0;
        foreach ($trax as $key => $data) {
            $mp_order = $data['iws_order'];
        }
        $this->logger_event->info('RegisterPayment - Order IWS '.$mp_order);

        //Se obtienen parametros de configuración por Store        
        $configData = $this->helperRegister->getConfigParams($storeScope, $storeManager->getStore($order->getStoreId())->getCode()); 
        $serviceUrl = $this->helperRegister->getServiceUrl($configData, 'registerpayments');


        if($payload){
            $storecode = $storeManager->getStore($order->getStoreId())->getCode();
            $this->logger_event->info('beginRegisterPayment - Inicio');                            
            $this->helperRegister->beginRegisterPayment($mp_order, $configData, $payload, $serviceUrl, $order, $storecode, 0);
            $this->logger_event->info('beginRegisterPayment - Fin');
        } else{
            $this->logger_event->info('RegisterPayment - Se ha producido un error al cargar la información de la orden en iws');
        }
        


    }
}