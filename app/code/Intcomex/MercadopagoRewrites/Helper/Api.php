<?php


use Magento\Store\Model\ScopeInterface;

namespace Intcomex\MercadopagoRewrites\Helper;

/**
 * Class Api
 * @package Intcomex\MercadopagoRewrites\Helper
 */
class Api
{
    const USER_API = 'payment/mercadopago/username_api';

    const PASSWORD_API = 'payment/mercadopago/password_api';


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