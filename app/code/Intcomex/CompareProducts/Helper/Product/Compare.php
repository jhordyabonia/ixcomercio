<?php

namespace Intcomex\CompareProducts\Helper\Product;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Compare extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    public function __construct(
        ScopeConfigInterface $config
    ){
        $this->config = $config;
    }

    public function canShow()
    {
        
        $isEnabled = $this->config->getValue("compare_product/general/compareEnable", ScopeInterface::SCOPE_STORE);
        $categorys = $this->config->getValue("compare_product/general/compareCategory", ScopeInterface::SCOPE_STORE);
        if ($isEnabled) {
            return $isEnabled;
        }
    }
}
