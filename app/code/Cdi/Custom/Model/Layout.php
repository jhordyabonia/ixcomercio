<?php
namespace Cdi\Custom\Model;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Layout  implements ObserverInterface
{
    protected $_logger;
    protected $dataHelper;

    public function __construct (
        \Magento\Framework\App\Action\Context $context,
        \Cdi\Custom\Helper\Data $helperData,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_logger = $logger;
        $this->helperData = $helperData;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $debug = (bool) $this->helperData->getStoreConfig('general/store_information/custom_log_layout');
        if($debug){
            $xml = $observer->getEvent()->getLayout()->getXmlString();
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/layout_block.xml');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($xml);
            return $this;
        }
    }
}