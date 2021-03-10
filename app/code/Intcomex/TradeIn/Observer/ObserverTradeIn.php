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

        /** @var \Magento\Framework\App\Action\Action $controller */
        $transport = $observer->getEvent()->getTransport();
        $order = $transport->getOrder();
        if ($order != null) 
        {
         if($order->getCouponCode()!=''){
             $prefijoCupon = $scopeConfig->getValue('tradein/general/prefijo_cupon',ScopeInterface::SCOPE_STORE);
             $cupon = strpos($order->getCouponCode(), $prefijoCupon);
             if ($cupon !== false) {
                 $transport['couponCode'] = 'display:block';
                }else{
                $transport['couponCode'] = 'display:none';
            }
         }
        }
    }
}