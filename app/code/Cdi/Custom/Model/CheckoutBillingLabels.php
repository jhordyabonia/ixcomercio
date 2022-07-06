<?php

namespace Cdi\Custom\Model;

use \Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;

class CheckoutBillingLabels implements ConfigProviderInterface
{
    protected $scopeConfig;

    protected $storeManager;

    protected $paymentMethod;

    protected $paymentHelper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Helper\Data $paymentHelper
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }
    public function getConfig()
    {   
        $storeId = $this->storeManager->getStore()->getId();

        $customLabelCheckout['cdi_checkout_identification'] =  $this->scopeConfig->getValue('customer/address/billing_identification_label',ScopeInterface::SCOPE_STORE, $storeId);
        $customLabelCheckout['cdi_checkout_name_label'] =  $this->scopeConfig->getValue('customer/address/billing_name_label',ScopeInterface::SCOPE_STORE, $storeId);
        $customLabelCheckout['cdi_checkout_address_label'] =  $this->scopeConfig->getValue('customer/address/billing_address_label',ScopeInterface::SCOPE_STORE, $storeId);
        
        return $customLabelCheckout;
    }
}