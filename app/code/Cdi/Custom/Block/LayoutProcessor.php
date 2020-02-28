<?php
namespace Cdi\Custom\Block;
 
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
 
class LayoutProcessor implements LayoutProcessorInterface
{
    public function process($jsLayout)
    {
        $jsLayout['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children']
        ['payments-list']['children']['checkmo-form']['children']
        ['form-fields']['children']['postcode']['sortOrder'] = 71;
        $jsLayout['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children']
        ['payments-list']['children']['checkmo-form']['children']
        ['form-fields']['children']['country_id']['sortOrder'] = 81;
        return $jsLayout;
    }
}