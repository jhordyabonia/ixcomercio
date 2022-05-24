<?php

namespace Intcomex\Checkout\Block\Checkout;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class LayoutProcessor implements LayoutProcessorInterface
{
    const CFDI_ATTRIBUTE_CODE = 'cfdi';
    const REGIMEN_FISCAL_ATTRIBUTE_CODE = 'regimen_fiscal';
    const RFC_ATTRIBUTE_CODE = 'rfc';
    const HOMEDICS_STORE_CODE = 'homedicsmx_store_view';

    /**
     * @var ScopeConfigInterface
     */
    private $config;
    private $storeManager;

    public function __construct(
        ScopeConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        //Esconder custom attributes en shipping form
        foreach ($this->getCustomAttributes() as $attribute) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children'][$attribute]['visible'] = false;
        }

        $configuration = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['payments-list']['children'];

        /* Modicar campos en billing address form */
        foreach ($configuration as $paymentGroup => $groupConfig) {
            if (isset($groupConfig['component']) AND $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {

                /* Esconder o mostrar custom attributes si estan habilitados desde el config */
                foreach ($this->getCustomAttributes() as $attribute) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']
                    ['children'][$attribute]['visible'] = $this->isAttributeEnabled($attribute);
                }

                if ($this->getStoreCode() == self::HOMEDICS_STORE_CODE) {
                    /* Cambiar label de nombre a Razon social  */
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']["firstname"]["label"] = __('Name or corporate name');

                    /* Mover el campo nombre al primer lugar */
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']["firstname"]["sortOrder"] = 1;

                    /* Esconder el campo identificacion */
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']["identification"]["visible"] = false;

                    /* Setear las validaciones solo para los custom attributes */
                    foreach ($this->getCustomAttributes() as $attribute) {
                        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                        ['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']
                        ['children'][$attribute]['validation']['required-entry'] = true;
                    }
                }
            }
        }

        return $jsLayout;
    }

    /**
     * @return array
     */
    public function getCustomAttributes() {
        return [
            self::CFDI_ATTRIBUTE_CODE,
            self::REGIMEN_FISCAL_ATTRIBUTE_CODE,
            self::RFC_ATTRIBUTE_CODE
        ];
    }

    /**
     * @return float
     * @param string $attribute
     */
    private function isAttributeEnabled(string $attribute): float
    {
        return (float)$this->config->getValue("intcomex_checkout/general/$attribute", ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }
}