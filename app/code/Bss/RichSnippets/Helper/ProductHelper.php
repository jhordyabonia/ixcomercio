<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RichSnippets
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RichSnippets\Helper;

/**
 * Class ProductHelper
 * @package Bss\RichSnippets\Helper
 */
class ProductHelper
{
    /**
     * @var object
     */
    public $reviewsCollection;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productLoader;
    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    private $reviewsColFactory;
    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    private $reviewFactory;

    /**
     * ProductHelper constructor.
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Catalog\Model\ProductFactory $productLoader
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $productLoader,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->dateTime = $dateTime;
        $this->stockRegistry = $stockRegistry;
        $this->productLoader = $productLoader;
        $this->reviewsColFactory = $collectionFactory;
        $this->reviewFactory = $reviewFactory;
    }

    /**
     * @param object $product
     * @return bool
     */
    public function isNew($product)
    {
        $fromDate = $product->getNewsFromDate();
        $toDate   = $product->getNewsToDate();

        if (!$fromDate || !$toDate) {
            return false;
        }

        $currentTime = $this->dateTime->gmtDate();
        $fromDate = $this->dateTime->gmtDate(null, $fromDate);
        $toDate = $this->dateTime->gmtDate(null, $toDate);

        $currentDateGreaterThanFromDate = (bool)($fromDate < $currentTime);
        $currentDateLessThanToDate = (bool)($toDate > $currentTime);

        if ($currentDateGreaterThanFromDate && $currentDateLessThanToDate) {
            return true;
        }
        return false;
    }

    /**
     * Get Stock item
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($product)
    {
        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        return $stockItem;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @return mixed
     */
    public function getRatingSummary($productId, $storeId)
    {
        $product = $this->productLoader->create()->load($productId);
        $this->reviewFactory->create()->getEntitySummary($product, $storeId);
        $arrRating['count'] = $product->getRatingSummary()->getReviewsCount();
        $arrRating['value'] = $product->getRatingSummary()->getRatingSummary();
        return $arrRating;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @return \Magento\Review\Model\ResourceModel\Review\Collection|object
     */
    public function getReviewsCollection($productId, $storeId)
    {
        if (null === $this->reviewsCollection) {
            $this->reviewsCollection = $this->reviewsColFactory->create()
            ->addStoreFilter($storeId)
            ->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $productId
            )->setDateOrder();
        }
        return $this->reviewsCollection;
    }

    /**
     * Get category collection
     *
     * @param bool $isActive
     * @param bool $level
     * @param bool $sortBy
     * @param bool $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }
}