<?php

namespace Cdi\Custom\Plugin\Checkout\Model\Checkout;

class LayoutProcessor
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['authentication'] = $jsLayout['components']['checkout']['children']['authentication'];

        $jsLayout['components']['checkout']['children']['authentication']['config']['componentDisabled'] = true;

        $shippingFieldsetChildren = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

        $shippingFieldsetChildren['firstname']['validation']['letters-allow-accent-mark'] = true;
        $shippingFieldsetChildren['firstname']['validation']['max_text_length'] = 15;
        $shippingFieldsetChildren['lastname']['validation']['letters-allow-accent-mark'] = true;
        $shippingFieldsetChildren['lastname']['validation']['max_text_length'] = 40;
        $shippingFieldsetChildren['telephone']['validation']['validate-number'] = true;
        $shippingFieldsetChildren['telephone']['validation']['max_text_length'] = 20;
        $shippingFieldsetChildren['street']['children'][0]['validation']['max_text_length'] = 140;
        $shippingFieldsetChildren['identification']['validation']['max_text_length'] = 20;
        $shippingFieldsetChildren['identification']['validation']['validate-number'] = true;
        $shippingFieldsetChildren['customer-email']['validation']['max_text_length'] = 70;

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $shippingFieldsetChildren;
        
        return $jsLayout;
    }
}