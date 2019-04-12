<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cdi\Custom\Block\Category;

/**
 * Class View
 * @api
 * @package Magento\Catalog\Block\Category
 * @since 100.0.2
 */
class View extends \Magento\Catalog\Block\Category\View{
    
	public function getCategoryProducts($category){
        $products = $category->getProductCollection();
        $products->addAttributeToSelect('*');
        return $products;
    }
}