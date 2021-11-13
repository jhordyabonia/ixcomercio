<?php
namespace Intcomex\GridImages\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{


    public function __construct(){

    }

    /**
     * Get config view gallery
     *
     * @return string
     */

    public function getViewGallery(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

        $view_gallery =  $_scopeConfig->getValue('catalog/general/catalog_gridimage',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    
        return $view_gallery;   
    
    }
}