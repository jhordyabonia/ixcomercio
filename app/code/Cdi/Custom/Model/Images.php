<?php

namespace Cdi\Custom\Model;

/**
 * Status
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Images
{
    
    public static function getAvailableImages()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		$dirs = array(
			'jam' => "{$directory->getRoot()}/app/design/frontend/Cdi/custom/web/images/attributes/",
			'sol' => "{$directory->getRoot()}/app/design/frontend/Cdi/solrepublic/web/images/attributes/",
			'marley' => "{$directory->getRoot()}/app/design/frontend/Cdi/marley/web/images/attributes/",
		);
		
		$images = array();
		
		foreach($dirs as $site => $dir){
			$ficheros  = scandir($dir);
			$images[$site] = array();
			foreach($ficheros as $image){
				if($image == '.' || $image == '..') continue;
				$name = explode('.', $image);
				$images[$site][$image] = $name[0];
			}
		}
		return $images;
    }
}
