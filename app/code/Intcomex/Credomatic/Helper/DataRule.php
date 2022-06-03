<?php
namespace  Intcomex\Credomatic\Helper;

use Magento\Store\Model\ScopeInterface;

class DataRule extends \Magento\Framework\App\Helper\AbstractHelper{ 

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface  $storeManagerInterface
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    public function isBinRule($appliedRuleIds){

        // if($appliedRuleIds == $this->_scopeConfig->getValue('payment/credomatic/cart_rule_id',ScopeInterface::SCOPE_STORE) && $this->_scopeConfig->getValue('payment/credomatic/bincampaign_active',ScopeInterface::SCOPE_STORE) == 1){
        //     return true;
        // }else{
        //     return false;
        // }
        return true;
    }
    
}
