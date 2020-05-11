<?php
namespace Cdi\Custom\Plugin\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as MagentoLayoutProcessor;


class LayoutProcessor
{

    public function afterProcess(MagentoLayoutProcessor $subject, $jsLayout)
    {   

        return $jsLayout;
    }
}