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
namespace Bss\RichSnippets\Plugin;

class ProductOffers
{
    public function __construct(
        \Bss\RichSnippets\Helper\Data $helper,
        \Magento\Framework\View\Page\Title $pageTitle,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository
    ) {
        $this->_storeManager = $storeManager;
        $this->stockItemRepository = $stockItemRepository;
        $this->helper = $helper;
        $this->coreRegistry = $registry;
        $this->pageTitle = $pageTitle;
    }

    public function afterToHtml($subject, $result)
    {
        $html = '';
        if ($this->getCurrentCategory() && $this->helper->getEnable() && $this->helper->getEnableNameCategory()) {
            $productCollection = $subject->getLoadedProductCollection();
            $dataProduct = $this->getDataProduct($productCollection);

            $enableDescription = $this->helper->getEnableDescriptionCategory();
            $description = $this->getCurrentCategory()->getDescription();


            $html = '<script type="application/ld+json">{';
            $html .= '"@context": "http://schema.org/",';
            $html .= '"@type": "WebPage",';
            $html .= '"name": "'.$subject->escapeHtml($this->getTitlePage()).'",';
            if ($enableDescription == '1' && $description != null) {
                $html .= '"description": "'.$subject->escapeHtml($description).'",';
            }
            $html .= '"mainEntity":'.$this->helper->jsonEncode($dataProduct);
            $html .= '}</script>';
        }

        return $result.$html;
    }


    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }
    /**
     * Get title
     *
     * @return mixed
     */
    public function getTitlePage()
    {
        return $this->pageTitle->getShort();
    }


    /**
     * @param string $productId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItem($productId)
    {
        return $this->stockItemRepository->getStockItem($productId);
    }


    /**
     * Get helper
     *
     * @return \Bss\RichSnippets\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Retrieve current category model object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDataProduct($productCollection)
    {
        $itemOffered = [];

        $currentCurrency = $this->getCurrentCurrency();
        $productOffer = $this->getHelper()->isSetFlag(
            \Bss\RichSnippets\Helper\Data::RICH_CATEGORY_PRODUCT_OFFERS
        );

        if ($productCollection->getSize()) {
            foreach ($productCollection as $product) {
                $ratingCount = $product->getReviewsCount();
                $ratingValue = $product->getRatingSummary();
                $stockStatus = $this->getStockItem($product->getId());
                if ($stockStatus->getIsInStock()) {
                    $stockStatusString = 'http://schema.org/InStock';
                } else {
                    $stockStatusString = 'http://schema.org/OutOfStock';
                }

                $description = $product->getShortDescription();
                $description = strip_tags($description);
                $description = str_replace('"', '\'', $description);
                $productImage = $this->helper->getProductImage($product);
                $dataToAdd = [
                    "@type" => "Product",
                    "name" => $product->getName(),
                    "image" => $productImage,
                    "sku" => $product->getSku(),
                    "description" => $description
                ];
                if ($ratingValue) {
                    $dataToAdd = $this->helper->processProductRating($dataToAdd, $ratingValue, $ratingCount);
                }
                if ($productOffer) {
                    $dataToAdd["offers"] = [
                        "@type" => "Offer",
                        "price" => $product->getFinalPrice(),
                        "priceCurrency" => $currentCurrency,
                        "url" => $product->getProductUrl(),
                        "availability" => $stockStatusString
                    ];
                }
                if (!isset($dataToAdd["offers"]) && !isset($dataToAdd["aggregateRating"])) {
                    $dataToAdd = [];
                }
                $itemOffered[] = $dataToAdd;
            }
        }
        $dataProduct = [];
        if (!empty($itemOffered)) {
            $dataProduct = [
                "@type" => "WebPageElement",
                "offers" => [
                    "@type" => "Offer",
                    "itemOffered" => $itemOffered
                ]
            ];
        }

        return $dataProduct;
    }
}
