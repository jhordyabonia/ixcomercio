<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block;

use ArrayIterator;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MagePal\GoogleTagManager\DataLayer\ProductData\ProductImpressionProvider;
use MagePal\EnhancedEcommerce\Helper\Data;
use MagePal\GoogleTagManager\Helper\Data as GtmHelper;

/**
 * Class CatalogLayer
 * @package MagePal\EnhancedEcommerce\Block
 */
class CatalogLayer extends DataLayer
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Catalog Product collection
     *
     * @var AbstractCollection
     */
    protected $_productCollection;

    /**
     * Variable is used to turn on/off the output of _getProductCollection for cross-sells
     *
     * @var bool
     */
    protected $_showCrossSells = true;

    /** @var CatalogHelper */
    protected $catalogHelper;

    /**
     * @var Layer
     */
    protected $catalogLayer;

    /**
     * @var ProductImpressionProvider
     */
    private $productImpressionProvider;

    /**
     * CatalogLayer constructor.
     * @param Context $context
     * @param Resolver $layerResolver
     * @param Registry $registry
     * @param CatalogHelper $catalogHelper
     * @param GtmHelper $gtmHelper
     * @param Data $eeHelper
     * @param ProductImpressionProvider $productImpressionProvider
     * @param array $data
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        Resolver $layerResolver,
        Registry $registry,
        CatalogHelper $catalogHelper,
        GtmHelper $gtmHelper,
        Data $eeHelper,
        ProductImpressionProvider $productImpressionProvider,
        array $data = []
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->catalogLayer = $layerResolver->get();
        $this->registry = $registry;

        parent::__construct($context, $gtmHelper, $eeHelper, $data);
        $this->productImpressionProvider = $productImpressionProvider;
    }

    /**
     * Retrieves a current category
     *
     * @return Category
     */
    public function getCurrentCategory()
    {
        /** @var Category $category */
        $category = null;

        if ($this->catalogLayer) {
            $category = $this->catalogLayer->getCurrentCategory();
        } elseif ($this->registry->registry('current_category')) {
            $category = $this->registry->registry('current_category');
        }
        return $category;
    }

    /**
     * Retrieves name of the current category
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCurrentCategoryName()
    {
        if (!$this->getShowCategory()) {
            return '';
        }
        /** @var Category $category */
        $category = $this->getCurrentCategory();

        if ($category && $this->_storeManager->getStore()->getRootCategoryId() != $category->getId()) {
            return $category->getName();
        }

        return '';
    }

    /**
     * Retrieve loaded category collection
     *
     * @param bool $reload
     * @return AbstractCollection | null
     * @throws LocalizedException
     */
    protected function _getProducts($reload = false)
    {
        /** @var Category $category */
        $category = $this->getCurrentCategory();

        if ($category
            && (
                $category->getDisplayMode() === null
                || in_array($category->getDisplayMode(), [CategoryModel::DM_MIXED, CategoryModel::DM_PRODUCT], true)
            )
        ) {
            return $this->_getProductCollection($reload);
        }

        return null;
    }

    /**
     * Retrieve loaded category collection
     *
     * @param bool $reload
     * @return AbstractCollection | null
     * @throws LocalizedException
     */
    protected function _getProductCollection($reload = false)
    {
        if (true === $reload) {
            $this->_productCollection = null;
        }

        $block = false;

        if ($this->getBlockName()) {
            $block = $this->getListBlock();
        }

        if (!$block) {
            return $this->_productCollection;
        }

        /* For catalog list and search results
         * Expects getListBlock as \Magento\Catalog\Block\Product\ListProduct
         */
        if (null === $this->_productCollection) {
            $this->_productCollection = $block->getLoadedProductCollection();
        }

        /* For collections of cross/up-sells and related
         * Expects getListBlock as one of the following:
         * \Magento\TargetRule\Block\Catalog\Product\ProductList\Upsell | _linkCollection
         * \Magento\TargetRule\Block\Catalog\Product\ProductList\Related | _items
         * \Magento\TargetRule\Block\Checkout\Cart\Crosssell | _items
         * \Magento\Catalog\Block\Product\ProductList\Related | _itemCollection
         * \Magento\Catalog\Block\Product\ProductList\Upsell | _itemCollection
         * \Magento\Checkout\Block\Cart\Crosssell, | setter items
         */
        if ($this->_showCrossSells && (null === $this->_productCollection)) {
            //return array on EE
            $itemCollection = $block->getItemCollection();

            if (is_array($itemCollection) && count($itemCollection)) {
                $this->_productCollection = new ArrayIterator($itemCollection);
            /*
            $this->_productCollection = $this->dataCollectionFactory->create();
            foreach ($itemCollection as $item) {
                $this->_productCollection->addItem($item);
            }
            */
            } elseif (is_object($itemCollection)) {
                $this->_productCollection = $itemCollection;
            }
        }

        // Support for CE
        if ((null === $this->_productCollection) && $this->isRelatedOrCrosssell()) {
            $itemCollection = $block->getItems();

            if (is_array($itemCollection) && count($itemCollection)) {
                $this->_productCollection = new ArrayIterator($itemCollection);
            } elseif (is_object($itemCollection)) {
                $this->_productCollection = $itemCollection;
            }
        }

        return  $this->_productCollection;
    }

    /**
     * @return bool
     */
    protected function isRelatedOrCrosssell()
    {
        return (
            $this->getBlockName() == 'catalog.product.related' || $this->getBlockName() == 'checkout.cart.crosssell'
        );
    }

    /**
     * Returns an instance of an assigned block via a layout update file
     *
     * @return Template
     * @throws LocalizedException
     */
    public function getListBlock()
    {
        return $this->getLayout()->getBlock($this->getBlockName());
    }

    /**
     * @return CatalogHelper
     */
    public function getCatalogHelper()
    {
        return $this->catalogHelper;
    }

    /**
     * Get bread crumb path
     *
     * @return array
     */
    protected function getBreadCrumbPath()
    {
        $titleArray = [];
        $breadCrumbs = $this->catalogHelper->getBreadcrumbPath();

        foreach ($breadCrumbs as $breadCrumb) {
            $titleArray[] = $breadCrumb['label'];
        }

        return $titleArray;
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->registry->registry('product'));
        }

        return $this->getData('product');
    }

    /**
     * @param $collection
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getProductImpressions($collection)
    {
        $products = [];
        $position = 1;

        if (is_object($collection) && $collection->count()) {
            foreach ($collection as $product) {
                $item = [
                    'name' => $product->getName(),
                    'id' => $product->getSku(),
                    'price' => $this->formatPrice($product->getFinalPrice()),
                    //'brand' => 'Google',
                    //'variant' => 'Gray',
                    'list' => $this->getListType(),
                    'position' => $position++,
                    'p_id' => $product->getId()
                ];

                if ($category = $this->getCurrentCategoryName()) {
                    $item['category'] = $category;
                }

                $products[] = $this->productImpressionProvider
                    ->setListType($this->getListType())
                    ->setItemData($item)
                    ->setProduct($product)
                    ->getData();
            }
        }

        return (array) $products;
    }
}
