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
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\Order $modelOrder,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        //Define el log
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron_credomatic.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        //Params
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
        $this->_modelOrder = $modelOrder;
        $this->_curl = $curl;
    }



    public function execute() 
    {
        //payment methods.
        $paymentMethod = array("credomatic", "credomaticvisa", "credomaticmastercard");

        $collection = $this->_orderCollectionFactory->create()->addFieldToFilter('status','pending_payment');
        $collection->getSelect()
            ->join(
                ["sop" => "sales_order_payment"],
                'main_table.entity_id = sop.parent_id',
                array('method')
            )
            ->where('sop.method in (?)',$paymentMethod);
            $collection->setOrder(
                'created_at',
                'desc'
            );
        $this->logger->info('Inicia Cron de órdenes');

        foreach($collection as $order){
            $urlQueryApi =  $this->scopeConfig->getValue('payment/credomatic/url_api',ScopeInterface::SCOPE_STORE, $order->getStoreId());
            $apiUsername =  $this->scopeConfig->getValue('payment/credomatic/usuario',ScopeInterface::SCOPE_STORE, $order->getStoreId());
            $apiPassword =  $this->scopeConfig->getValue('payment/credomatic/password',ScopeInterface::SCOPE_STORE, $order->getStoreId());
            $timeoutCancelOrder = $this->scopeConfig->getValue('payment/credomatic/timeoutordercron',ScopeInterface::SCOPE_STORE, $order->getStoreId());

            $minutos = (strtotime($order->getCreatedAt())-strtotime(date('Y-m-d H:i:s')))/60;
            $minutos = abs($minutos); $minutos = floor($minutos);
            
            if(!$this->getApiDataById( $apiUsername, $apiPassword, $urlQueryApi, $order->getIncrementId()) && $minutos>$timeoutCancelOrder){
                $this->logger->info('Se valida la orden '.$order->getIncrementId());
                $this->logger->info('La orden '.$order->getIncrementId().' lleva mas de 1 hora de creada '.$minutos);
                //$this->cancelOrder($order);
                $this->logger->info('La orden '.$order->getIncrementId().' cancelada');
            }

            $this->processOrder($response,$response['authcode'],$order);
        }

        $this->logger->info('Termina Cron de órdenes');
    }

    /**
     * @param $order;
     * @return true|false;
     */
    public function eventCheckoutSucess($order)
    {   //9000000608
        $this->eventManager->dispatch('checkout_onepage_controller_success_action', ['order'=>$order]);
        $this->logger->info('Credomatic success order cron - event checkout_onepage_controller_success_action');
    }

    /**
     * @param $orderId;
     * @return $responseObject;
     */
    public function getApiDataById($user, $pass, $url, $orderId)
    {
        $params = array(
            'username' => $user,
            'password' => $pass,
            'order_id' => $orderId
        );

        try{
            if($params['username'] && $params['password']){
                $this->_curl->post($url, $params); 
                $dataResp =  $this->_curl->getBody();
                $response = json_decode($dataResp,true);
                $this->logger->info('Respuesta servicio Credomatic: ' . print_r($response, true));
            }
            

            if(!$response){
                return false;
            }
            return $response;

        } catch (\Exception $e) {
            $this->logger->info('Cron_exception: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * @param $body, $transactionId, $order;
     * @return true|false
     */
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
            $this->eventCheckoutSucess($order);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $body, $order;
     * @return true|false
     */
    public function cancelOrder($order){
        try {
            $this->logger->info('Se procede a cancelar la '.$order->getIncrementId().' desde el cron ');
            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->addStatusToHistory($order->getStatus(), 'Se procede a cancelar la orden desde el cron');
            $payment = $order->getPayment();
            $order->setIsPaidCredo('No');
            $order->save(); 
            return true;
        } catch (\Exception $e) {
           return false;
        }
    }

}