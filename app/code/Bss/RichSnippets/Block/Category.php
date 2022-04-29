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
namespace Bss\RichSnippets\Block;

/**
 * Class Category
 *
 * @package Bss\RichSnippets\Block
 */
class Category extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\RichSnippets\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    protected $pageTitle;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;

    /**
     * @var \Magento\Catalog\Model\Layer\Category
     */
    private $layerCategory;

    /**
     * @var \Bss\RichSnippets\Model\Category\Image
     */
    protected $cateImage;

    /**
     * Category constructor.
     * @param \Bss\RichSnippets\Helper\Data $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\View\Page\Title $pageTitle
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository
     * @param \Magento\Catalog\Model\Layer\Category $layerCategory
     * @param \Bss\RichSnippets\Model\Category\Image $cateImage
     * @param array $data
     */
    public function __construct(
        \Bss\RichSnippets\Helper\Data $helper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Page\Title $pageTitle,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Layer\Category $layerCategory,
        \Bss\RichSnippets\Model\Category\Image $cateImage,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->coreRegistry = $registry;
        $this->pageTitle = $pageTitle;
        $this->context = $context;
        $this->layerCategory = $layerCategory;
        $this->cateImage = $cateImage;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        $productCollection = $this->layerCategory->getProductCollection();
        return $productCollection;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrency()
    {
        return $this->context->getStoreManager()->getStore()->getCurrentCurrencyCode();
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
     * Get type page
     *
     * @return string
     */
    public function getTypePage()
    {
        return $this->context->getRequest()->getFullActionName();
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
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDataProduct()
    {
        $itemOffered = [];
        $productCollection = $this->getProductCollection();

        $currentCurrency = $this->getCurrentCurrency();
        $productOffer = $this->getHelper()->isSetFlag(
            \Bss\RichSnippets\Helper\Data::RICH_CATEGORY_PRODUCT_OFFERS
        );
        if ($productCollection->getSize()) {
            foreach ($productCollection as $product) {
                $ratingCount = $product->getReviewsCount();
                $ratingValue = $product->getRatingSummary();
                if ($product->getData('is_salable')) {
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

                $dataToAdd = $this->helper->processProductRating($dataToAdd, $ratingValue, $ratingCount);
                if ($productOffer) {
                    $product->setData('category_ids', [$this->getCurrentCategory()->getId()]);
                    $dataToAdd["offers"] = [
                        "@type" => "Offer",
                        "price" => $product->getData('final_price'),
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

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryImage()
    {
        $categoryImage = $this->getCurrentCategory()->getImage();
        $mediaUrl = $this->getMediaUrl();
        $websiteImage = $this->getHelper()->getWebsiteImage();

        if ($categoryImage == null) {
            $categoryImage = $websiteImage;
            $categoryImage = $mediaUrl . 'bss/logo/' . $categoryImage;
        } else {
            $categoryImage = $this->cateImage->getUrl($this->getCurrentCategory());
        }
        return $categoryImage;
    }

    /**
     * Get media Url
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        $mediaDir = $this->context->getStoreManager()
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaDir;
    }
}
