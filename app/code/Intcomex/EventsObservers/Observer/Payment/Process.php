<?php

namespace Intcomex\EventsObservers\Observer\Payment;
 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Intcomex\EventsObservers\Helper\RegisterPayment;
use \Intcomex\EventsObservers\Helper\PlaceOrder;
use Trax\Ordenes\Model\IwsOrderFactory;
use Magento\Sales\Model\Order;

class Process implements ObserverInterface
{
    /**
    * @var helper
    */
    protected $helper;

    /**
    * @var helperplaceorder
    */
    protected $helper_placeorder;

    /**
     * @var logger
     */
    protected $logger;

    /**
     * @var iwsOrder
     */
    protected $iwsOrder;

    /**
    * Add constructor.
    * @param helper $helper
 
    */
    public function __construct(
        RegisterPayment $helper,
        PlaceOrder $helper_placeorder,
        \Trax\Ordenes\Model\IwsOrderFactory  $iwsOrder)
    {
        $this->helper              = $helper;
        $this->helper_placeorder   = $helper_placeorder;
        $this->iwsOrder            = $iwsOrder;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/events_sales_order.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
    }

    /**
     * Below is the method that will fire whenever the event runs!
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->logger->info("Se ejecuta el observador 'Payment Process'");
        
        $order = $observer->getOrder();
        $stateProcessing = $order::STATE_PROCESSING;
        $statePending = Order::STATE_PENDING_PAYMENT;
        $payment = $order->getPayment();
        $method  = $payment->getMethodInstance();
        $this->logger->info("Está Orden tiene state ->" . $order->getState() . " y status ->" . $order->getStatus() );
        if (
            ($order->getState() == $stateProcessing 
            //&& $order->getOrigData('state') != $stateProcessing
            && $payment->getMethod() != 'pasarela_bancomer') || 
                (
                $order->getState() == $statePending && $payment->getMethod() == 'mercadopago_custom'
                )
            ) {
                $storeScope    = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();     
                $storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                                
                //Se obtienen parametros de configuración por Store        
                $configData = $this->helper->getConfigParams($storeScope, $storeManager->getStore($order->getStoreId())->getCode()); 
                $this->logger->info('RegisterPayment - Se obtienen parámetros de configuración');
                $this->logger->info(print_r($configData,true));
                $serviceUrl = $this->helper->getServiceUrl($configData, 'registerpayments');   
                $this->logger->info('RegisterPayment - url '.$serviceUrl);
                
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('iws_order'); 
                //Select Data from table
                $sql = "Select * FROM " . $tableName." where order_increment_id='".$order->getIncrementId()."'";

                $this->logger->info('RegisterPayment - iws_order sql '.$sql);

                if(!$connection){
                    $this->logger->info('RegisterPayment - No hay conexion a la tabla  iws_order');
                }else{

                    $trax = $connection->fetchAll($sql); 

                    $this->logger->info('RegisterPayment - iws_order result '.json_encode($trax));

                    $mp_order = 0;
                    foreach ($trax as $key => $data) {
                        $mp_order = $data['iws_order'];
                    }
                    $this->logger->info('RegisterPayment - Order IWS '.$mp_order);
                    
                    if($mp_order!=0){
                        try{                       
                            $this->logger->info('Consultamos el payload');
                            $transactionId = $this->_setTransactionId($order);
                            $payload = $this->helper->loadPayloadService(
                                        $order->getId(), 
                                        $payment->getAmountOrdered(), 
                                        $transactionId,
                                        $transactionId, 
                                        '', 
                                        $payment->getMethod(), 
                                        $storeManager->getWebsite($storeManager->getStore($order->getStoreId())->getWebsiteId())->getCode()
                                );
                            $this->logger->info('RegisterPayment - PayLoad '. $payload);
                                
                            if($payload){
                                $storecode = $storeManager->getStore($order->getStoreId())->getCode();
                                $this->logger->info('beginRegisterPayment - Inicio');                            
                                $this->helper->beginRegisterPayment($mp_order, $configData, $payload, $serviceUrl, $order, $storecode, 0);
                                $this->logger->info('beginRegisterPayment - Fin');
                            } else{
                                $this->logger->info('RegisterPayment - Se ha producido un error al cargar la información de la orden en iws');                                                    }
                            
                        } catch(Exception $e){
                            $this->logger->info('RegisterPayment - Se ha producido un error: '.$e->getMessage());
                        }
                    } else if ($mp_order==0) {
                            try{
                                $configDataPlace = $this->helper_placeorder->getConfigParams($storeScope, $storeManager->getStore($order->getStoreId())->getCode()); 
                                $this->logger->info('PlaceOrder process - Se obtienen parámetros de configuración');
                                $this->logger->info(print_r($configDataPlace,true));
                                $serviceUrlPlace = $this->helper_placeorder->getServiceUrl($configDataPlace, $order->getIncrementId());   
                                $this->logger->info('PlaceOrder process - url '.$serviceUrlPlace);
                                $this->logger->info('Consultamos el placeload');                                                        
                                $placeload = $this->helper_placeorder->loadPayloadService($order, $storeManager->getWebsite($storeManager->getStore($order->getStoreId())->getWebsiteId())->getCode(), $configDataPlace['store_id'], $configDataPlace['porcentaje_impuesto'], $configDataPlace['producto_impuesto']);
                                $this->logger->info('loadPayloadService - Fin');
                                if($placeload){
                                    $this->logger->info('beginPlaceOrder - Inicio');
                                    $storecode = $storeManager->getStore($order->getStoreId())->getCode();
                                    $place = $this->helper_placeorder->beginPlaceOrder($configDataPlace, $placeload, $serviceUrlPlace, $order, $storecode, 0);
                                    $this->logger->info('beginPlaceOrder - Find');
                                    if($place)
                                    {
                                        $this->logger->info('Consultamos el payload');
                                        $transactionId = $this->_setTransactionId($order);
                                        $payload = $this->helper->loadPayloadService(
                                                    $order->getId(), 
                                                    $payment->getAmountOrdered(), 
                                                    $transactionId,
                                                    $transactionId,
                                                    '', 
                                                    $payment->getMethod(), 
                                                    $storeManager->getWebsite($storeManager->getStore($order->getStoreId())->getWebsiteId())->getCode()
                                            );
                                        $this->logger->info('RegisterPayment - PayLoad '. $payload);
                                            
                                        if($payload){
                                            $storecode = $storeManager->getStore($order->getStoreId())->getCode();
                                            $this->logger->info('beginRegisterPayment - Inicio');                            
                                            $this->helper->beginRegisterPayment($mp_order, $configData, $payload, $serviceUrl, $order, $storecode, 0);
                                            $this->logger->info('beginRegisterPayment - Fin');
                                        } else{
                                            $this->logger->info('RegisterPayment - Se ha producido un error al cargar la información de la orden en iws');                                                    }
                                        
                                    }
                                } else {
                                    $this->logger->info('PlaceOrder process - Se ha producido un error al obtener match con Trax');
                                }
                            } catch(Exception $e){
                                $this->logger->info('PlaceOrder process - Se ha producido un error: '.$e->getMessage());
                            }
                        } else {
                            $this->logger->info('RegisterPayment - Se ha producido un error al conectarse al servicio. No se detectaron parametros de configuracion'); 
                        }
                    }
                }
    }

    /**
     * Set Transaction Id To Payment.
     * @return string
     */
    private function _setTransactionId($order)
    {
        $payment = $order->getPayment();
        $LastTransId = $payment->getCcTransId();
        if ($payment->getMethod()=='mercadopago_custom') {
            if (empty($payment->getCcTransId())) {
                $this->logger->info('Payment - Mercadopago_custom: orden '.$order->getIncrementId().' tiene pago con atributo CcTransId vacio.');
                $paymentResponse = $payment->getAdditionalInformation("paymentResponse");
                if (empty($paymentResponse)) {
                    $this->logger->info('Payment - Mercadopago_custom: PaymentResponse vacio : ' . $order->getIncrementId());   
                } else {
                    $LastTransId = $paymentResponse["id"];
                    $payment->setCcTransId($LastTransId);
                    $updateData  = $payment->save();
                    if ($updateData) {
                        $this->logger->info('Payment - Mercadopago_custom, Se actualizo el atributo CcTransId del pago con el numero de autorizacion : '. $LastTransId);
                    } else {
                        $this->logger->info('Payment - Mercadopago_custom, Se produjo un error al actualizar el atributo CcTransId del pago con el numero de autorizacion : '.$LastTransId);
                    }
                }
            } else {
                $LastTransId = $payment->getCcTransId();
            }
        } else {
            $LastTransId = empty($payment->getLastTransId()) ? $LastTransId : $payment->getLastTransId();
        }

        return $LastTransId;
    }
}
