<?php
namespace Intcomex\Credomatic\Cron;

use \Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;

class OrderStatus {
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $_orderCollectionFactory;
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        //Define el log
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron_credomatic.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        //Params
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->urlQueryApi =  $this->scopeConfig->getValue('payment/credomatic/url_api',ScopeInterface::SCOPE_STORE);
        $this->_curl = $curl;
    }



    public function execute() {
        $paymentMethod = 'credomatic';
        //Se debe cambiar a una consulta que permita agregar metodos de pago por array
		$collection = $this->_orderCollectionFactory->create()->addFieldToFilter('status','pending_payment');
         /* join with payment table */
            $collection->getSelect()
            ->join(
                ["sop" => "sales_order_payment"],
                'main_table.entity_id = sop.parent_id',
                array('method')
            )
            ->where('sop.method in ?',$paymentMethod); //E.g: ccsave

            $collection->setOrder(
                'created_at',
                'desc'
            );
        $this->logger->info('Inicia Cron de órdenes');
        foreach($collection as $order){
            
            // Llamado a la API de credomatic
            $username =  $this->scopeConfig->getValue('payment/credomatic/usuario',ScopeInterface::SCOPE_STORE,$order->getStoreId());
            $password =  $this->scopeConfig->getValue('payment/credomatic/password',ScopeInterface::SCOPE_STORE,$order->getStoreId());
            
            
            $minutos = (strtotime($order->getCreatedAt())-strtotime(date('Y-m-d H:i:s')))/60;
            $minutos = abs($minutos); $minutos = floor($minutos);
            if($minutos>60){
           // if($order->getIncrementId()==26000000014){
                $this->logger->info('Se valida la orden '.$order->getIncrementId());
                $this->logger->info('La orden '.$order->getIncrementId().' lleva mas de 1 hora de creada '.$minutos);

                 //validate transaction
                $params = array(
                    'username' => $username,
                    'password' => $password,
                    'order_id' => $order->getIncrementId()
                );

                $this->_curl->post($this->urlQueryApi, $params); 
                $dataResp =  $this->_curl->getBody();
                $this->logger->info('Respuesta servicio Credomatic');
                $xml=simplexml_load_string($dataResp);
                if(!empty($xml)&&isset($xml->transaction->action)){
                    $response = json_decode(json_encode((array)$xml->transaction->action), TRUE);
                    $this->logger->info(print_r($response,true));
                    if($response['response_code']!=100){
                         $this->cancelOrder($response,$order);
                    }else{ 
                         $this->processOrder($response,$response['authcode'],$order);
                    }
                }else{
                    $this->logger->info('No se encontro información de la transaccion en la pasarela para la orden: '.$order->getIncrementId());
                     $this->cancelOrder(false,$order);
                }

            }

        }
        $this->logger->info('Finaliza Cron de órdenes');
    }

    public function processOrder($body,$transactionId,$order){

        try {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $this->logger->info('Orden '.$order->getIncrementId().' cambiada a estado processing por el cron ');
            $order->addStatusToHistory($order->getStatus(), 'Orden cambiada a estado processing por el cron');
            $payment = $order->getPayment();
            $payment->setLastTransId($transactionId);
            $payment->setAdditionalInformation('payment_resp',json_encode($body));
            $order->setIsPaidCredo('Yes');
            $order->save();
            //Creo que esto se ejecuta desde un controlador
            //$this->_eventManager->dispatch('checkout_onepage_controller_success_action', ['obj' => $order]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function cancelOrder($body,$order){
        try {
            $this->logger->info('Se procede a cancelar la '.$order->getIncrementId().' desde el cron ');
            $order->addStatusToHistory($order->getStatus(), 'Se procede a cancelar la orden desde el cron');
            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $payment = $order->getPayment();
            if(isset($body['authcode'])){
                $payment->setLastTransId($body['authcode']);
            }
            if(!empty($body)){
                $payment->setAdditionalInformation('payment_resp',json_encode($body));
            }
            $order->setIsPaidCredo('No');
            $order->save(); 
        } catch (\Exception $e) {
           return false;
        }
    }


}