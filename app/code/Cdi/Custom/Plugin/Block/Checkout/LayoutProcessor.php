<?php
namespace Cdi\Custom\Plugin\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as MageLayoutProcessor;


class LayoutProcessor
{

    public function afterProcess(MagentoLayoutProcessor $subject, $jsLayout)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(print_r($jsLayout, true));
        /*
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children']
        )){
            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'] as $key => $payment) {
                if (isset($payment['children']['form-fields']['children']['company'])) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                    ['company']['sortOrder'] = 10;
                }
            }
        }
        */
        return $jsLayout;
    }
}