<?php
namespace Intcomex\Checkout\Plugin;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Eav\Model\Config;
class ConfigProviderPlugin extends \Magento\Framework\Model\AbstractModel
{
    private $checkoutSession;
    protected $scopeConfig;
    private $eavConfig;
    public function __construct(
        CheckoutSession $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Config $eavConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->eavConfig = $eavConfig;
    }
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        $attributeCfdi = $this->eavConfig->getAttribute('customer_address', 'cfdi');
        $attributeRegimenFiscal = $this->eavConfig->getAttribute('customer_address', 'regimen_fiscal');
        $result['cfdi_data'] = $attributeCfdi->getSource()->getAllOptions();
        $result['regimen_fiscal_data'] = $attributeRegimenFiscal->getSource()->getAllOptions();
        return $result;
    }
}