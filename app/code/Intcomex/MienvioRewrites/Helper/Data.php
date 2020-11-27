<?php
namespace Intcomex\MienvioRewrites\Helper;

use MienvioMagento\MienvioGeneral\Helper\Data as MainHelper;

class Data extends MainHelper{


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