<?php

namespace Intcomex\MercadopagoRewrites\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order;

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


    public function getOrdenByIncrementId($idOrden, $reintentos = 0)
    {
        $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($idOrden);

        if(!is_object($order) && $reintentos < 3 ){
            $this->getOrdenByIncrementId($idOrden, $reintentos++);
            $this->logger->info('Mercadopago Helper - orden '.$idOrden);
            $this->logger->info('Mercadopago Helper - reintentos '.$reintentos);
        }

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
}