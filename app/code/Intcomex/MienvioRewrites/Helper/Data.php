<?php

namespace Intcomex\MienvioRewrites\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;


class Data extends AbstractHelper
{

    const XML_PATH_KIT_URL = 'carriers/mienviokit/url';
    const XML_PATH_KIT_RETRIES = 'carriers/mienviokit/retries';
    const XML_PATH_KIT_EMAIL = 'carriers/mienviokit/email';
    const XML_PATH_KIT_DIMENSION = 'carriers/mienviokit/dimension';

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }


    public function getKitUrlService($storeId = null){
        return $this->getConfigValue(self::XML_PATH_KIT_URL , $storeId);
    }

    public function getKitRetries($storeId = null){
        return $this->getConfigValue(self::XML_PATH_KIT_RETRIES , $storeId);
    }

    public function getKitEmail($storeId = null){
        return $this->getConfigValue(self::XML_PATH_KIT_EMAIL , $storeId);
    }

    public function getKitDimension($storeId = null){
        return $this->getConfigValue(self::XML_PATH_KIT_DIMENSION , $storeId);
    } 

}