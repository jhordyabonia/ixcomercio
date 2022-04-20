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
		$collection = $this->_orderCollectionFactory->create()->addFieldToFilter('status','pending_payment');
         /* join with payment table */
            $collection->getSelect()
            ->join(
                ["sop" => "sales_order_payment"],
                'main_table.entity_id = sop.parent_id',
                array('method')
            )
            ->where('sop.method = ?',$paymentMethod); //E.g: ccsave

            $collection->setOrder(
                'created_at',
                'desc'
            );
        $this->logger->info('Inicia Cron de órdenes');
        foreach($collection as $order){
            
            $this->logger->info('Se valida la orden '.$order->getIncrementId());
            // Llamado a la API de credomatic
            $username =  $this->scopeConfig->getValue('payment/credomatic/usuario',ScopeInterface::SCOPE_STORE,$order->getStoreId());
            $password =  $this->scopeConfig->getValue('payment/credomatic/password',ScopeInterface::SCOPE_STORE,$order->getStoreId());

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
            $this->logger->info(print_r($xml->transaction->action,true));
            if(empty($xml)||!isset($xml->transaction)){
                return false;
            }

        }
        $this->logger->info('Finaliza Cron de órdenes');
    }
}