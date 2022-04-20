<?php
namespace Intcomex\Credomatic\Cron;

use \Psr\Log\LoggerInterface;

class OrderStatus {
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $_orderCollectionFactory;
    protected $logger;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        //Define el log
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron_credomatic.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        //Params
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }



    public function execute() 
    {
		$orders = $this->_orderCollectionFactory->create()->addFieldToFilter('status','pending_payment');
        $this->log('Inicia Cron de órdenes');
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
}