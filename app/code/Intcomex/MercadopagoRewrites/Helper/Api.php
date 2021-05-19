<?php

namespace Intcomex\MercadopagoRewrites\Helper;

use Magento\Store\Model\ScopeInterface;

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


    public function  __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        
    ){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/apiMercadopago.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $this->_scopeConfig = $scopeConfig; 
    }


    public function getOrdenByIncrementId($idOrden)
    {

        $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($idOrden);

        return $order;

    }
}