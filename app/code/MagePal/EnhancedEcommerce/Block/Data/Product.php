<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Data;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use MagePal\EnhancedEcommerce\Block\CatalogLayer;
use MagePal\GoogleTagManager\Model\DataLayerEvent;

/**
 * Class Product
 * @package MagePal\EnhancedEcommerce\Block\Data
 */
class Product extends CatalogLayer
{
    const TYPE_GROUP = 'grouped';
    const TYPE_SIMPLE = 'simple';
    const TYPE_BUNDLE = 'bundle';
    const TYPE_VIRTUAL = 'virtual';
    const TYPE_CONFIGURABLE = 'configurable';

    /**
     * Add category data to datalayer
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _dataLayer()
    {
        /** @var $currentProduct ProductInterface */
        $currentProduct = $this->getProduct();

        if ($currentProduct) {
            $products = [];

            switch ($currentProduct->getTypeId()) {
                case static::TYPE_GROUP:
                    $products = $this->getGroupProducts();
                    $products[] = $this->getProductLayer($currentProduct);
                    break;
                case static::TYPE_CONFIGURABLE:
                    $products = $this->getConfigurableProducts();
                    $products[] = $this->getProductLayer($currentProduct);
                    break;
                case static::TYPE_BUNDLE:
                    $products = $this->getBundleProducts();
                    $products[] = $this->getProductLayer($currentProduct);
                    break;
                default:
                    $products[] = $this->getProductLayer($currentProduct);
            }

            $impressionsProductData = [
                'event' => DataLayerEvent::PRODUCT_DETAIL_EVENT,
                'ecommerce' => [
                    'currencyCode' => $this->getStoreCurrencyCode(),
                    'detail' => [
                        //'actionField' => ['list' => 'Apparel Gallery'],
                        'products' => $products,
                    ]
                ]
            ];

            $this->addCustomDataLayerByEvent(DataLayerEvent::PRODUCT_DETAIL_EVENT, $impressionsProductData);

            $relatedProduct = $this->getRelatedProduct();
            $upsellProduct = $this->getUpsellProduct();

            $list = array_merge($relatedProduct, $upsellProduct);

            if (!empty($list)) {
                $impressionsListData = [
                    'event' => DataLayerEvent::PRODUCT_IMPRESSION_EVENT,
                    'currencyCode' => $this->getStoreCurrencyCode(),
                    'ecommerce' => [
                        'impressions' => $list
                    ]
                ];

                $this->addCustomDataLayerByEvent(DataLayerEvent::PRODUCT_IMPRESSION_EVENT, $impressionsListData);
            }

            if (!empty($relatedProduct)) {
                $this->setImpressionList(
                    $this->_eeHelper->getRelatedListType(),
                    $this->_eeHelper->getRelatedClassName(),
                    $this->_eeHelper->getRelatedContainerClass()
                );
            }

            if (!empty($upsellProduct)) {
                $this->setImpressionList(
                    $this->_eeHelper->getUpsellListType(),
                    $this->_eeHelper->getUpsellClassName(),
                    $this->_eeHelper->getUpsellContainerClass()
                );
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getGroupProducts()
    {
        $product = $this->getProduct();
        $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);

        $products = [];
        foreach ($associatedProducts as $associatedProduct) {
            $products[] = $this->getProductLayer($associatedProduct);
        }

        return $products;
    }

    /**
     * @return array
     */
    protected function getBundleProducts()
    {
        $product = $this->getProduct();

        $associatedProducts = $product->getTypeInstance()->getSelectionsCollection(
            $product->getTypeInstance()->getOptionsIds($product),
            $product
        );

        $products = [];
        foreach ($associatedProducts as $associatedProduct) {
            $products[] = $this->getProductLayer($associatedProduct);
        }

        return $products;
    }

    /**
     * @return array
     */
    protected function getConfigurableProducts()
    {
        $product = $this->getProduct();
        $configProducts = $product->getTypeInstance(true)->getUsedProducts($product);

        //get options
        //$product->getTypeInstance(true)->getConfigurableOptions($product);

        $products = [];
        foreach ($configProducts as $configProduct) {
            $products[] = $this->getProductLayer($configProduct);
        }

        return $products;
    }

    /**
     * @param $product
     * @return array
     */
    public function getProductLayer($product)
    {
        $item = [
            'id' => $product->getSku(),
            'name' => $product->getName(),
            //'brand' => 'Google',
            //'variant' => 'Gray',
        ];

        if ($category = $this->getProductCategoryName()) {
            $item['category'] = $category;
        }

        if ($price = $this->formatPrice($product->getFinalPrice())) {
            $item['price'] = $price;
        }

        return $item;
    }

    /**
     * Get category name from breadcrumb
     *
     * @return string
     */
    protected function getProductCategoryName()
    {
        $categoryName = '';

        $categoryArray = $this->getBreadCrumbPath();
        if (count($categoryArray) > 1) {
            end($categoryArray);
            $categoryName = prev($categoryArray);
        } elseif ($this->getProduct()) {
            $category = $this->getProduct()->getCategoryCollection()->addAttributeToSelect('name')->getFirstItem();
            $categoryName = ($category) ? $category->getName() : '';
        }

        return $categoryName;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getRelatedProduct()
    {
        $this->setBlockName('catalog.product.related');
        $this->setListType($this->_eeHelper->getRelatedListType());
        return $this->getProductImpressions($this->_getProducts(true));
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getUpsellProduct()
    {
        $this->setBlockName('product.info.upsell');
        $this->setListType($this->_eeHelper->getUpsellListType());
        return $this->getProductImpressions($this->_getProducts(true));
    }
}
