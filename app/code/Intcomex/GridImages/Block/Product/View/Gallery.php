<?php
namespace Intcomex\GridImages\Block\Product\View;



use \Magento\Catalog\Api\ProductRepositoryInterfaceFactory;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{

    protected $_productRepositoryFactory;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
    ) {            
        $this->_productRepositoryFactory = $productRepositoryFactory;
    }



    public function getImages(){
        $product = $this->_productRepositoryFactory->create()
    ->getById($item->getProductId());
        $product->getData('image');
        $product->getData('thumbnail');
        $product->getData('small_image');


        return $product->getData('image');
    }
}