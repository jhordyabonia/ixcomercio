<?php
namespace Intcomex\TradeIn\Observer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class ObserverTradeIn implements ObserverInterface
{
    protected $customerRepository;
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

        $theme = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $mediaUrl = $theme->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );

        /** @var \Magento\Framework\App\Action\Action $controller */
        $transport = $observer->getEvent()->getTransport();
        $order = $transport->getOrder();
        $transport['img_alerta'] = $mediaUrl.'iconos_alerta/icono_'.$theme->getStore()->getCode().'.png';
        if ($order != null) 
        {
         if($order->getCouponCode()!=''){
             $prefijoCupon = $scopeConfig->getValue('tradein/general/prefijo_cupon',ScopeInterface::SCOPE_STORE);
             $cupon = strpos($order->getCouponCode(), $prefijoCupon);
             if ($cupon !== false) {
                 $transport['couponCode'] = 1;
            }
         }
        }
    }
}