<?php

namespace Intcomex\Managereindex\Plugin\Indexer;

use \Intcomex\Managereindex\Helper\Helper;
use Magento\Store\Model\StoreManagerInterface;

class IndexerPlugin
{
    public function __construct(
        \Intcomex\Managereindex\Helper\Helper $helper,
         StoreManagerInterface $storeManager
     ) {       
         $this->helper = $helper;
         $this->storeManager = $storeManager;       
     }    

    public function beforereindexAll($subjetc)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/debug_plugin_reindexall.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);        

        $storeId = $this->storeManager->getStore()->getId();
        if ( !$this->helper->isEnabled($storeId) && $subjetc->getId() != 'cataloginventory_stock'){
           $logger->info('reindexAll is Disable');
           exit();
        }
        else if($subjetc->getId() == 'cataloginventory_stock')
              $logger->info('reindexAll is Enable of ' . $subjetc->getId() );
    }

}