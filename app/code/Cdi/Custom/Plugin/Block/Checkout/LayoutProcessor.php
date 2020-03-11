<?php
namespace Cdi\Custom\Plugin\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as MagentoLayoutProcessor;


class LayoutProcessor
{

    public function afterProcess(MagentoLayoutProcessor $subject, $jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children']
        )){
            $fields = array(
                'firstname' => 20,
                'lastname' => 25,
                'telephone' => 30,
                'company' => 35,
                'country_id' => 40,
                'region' => 45,
                'region_id' => 46,
                'city' => 90,
                'street' => 95,
                'zone_id' => 100,
                'postcode' => 110
            );
            foreach($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $key => $payment){
                foreach($fields as $field => $sort){
                    if (isset($payment['children']['form-fields']['children'][$field])) {
                        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                        ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                        [$field]['sortOrder'] = $sort;
                    }
                }
            }
        }
        return $jsLayout;
    }
}