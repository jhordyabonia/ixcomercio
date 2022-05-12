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
 * Class Product
 *
 * @package Bss\RichSnippets\Block
 */
class Product extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\RichSnippets\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var $collectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    protected $pageTitle;
    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    private $productUrlPathGenerator;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;
    /**
     * @var \Bss\RichSnippets\Helper\ProductHelper
     */
    private $productHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Repository
     */
    protected $productAttributeRepository;

    /**
     * @var \Bss\SeoCore\Helper\Data
     */
    protected $seoCoreHelper;

    /**
     * Product constructor.
     * @param \Bss\RichSnippets\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\View\Page\Title $pageTitle
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
     * @param \Bss\RichSnippets\Helper\ProductHelper $productHelper
     * @param \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository
     * @param \Bss\SeoCore\Helper\Data $seoCoreHelper
     * @param array $data
     */
    public function __construct(
        \Bss\RichSnippets\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Page\Title $pageTitle,
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator,
        \Bss\RichSnippets\Helper\ProductHelper $productHelper,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Bss\SeoCore\Helper\Data $seoCoreHelper,
        array $data = []
    ) {
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->pageTitle = $pageTitle;
        $this->registry = $registry;
        $this->context = $context;
        $this->helper = $helper;
        $this->productHelper = $productHelper;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->seoCoreHelper = $seoCoreHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get Helper
     *
     * @return \Bss\RichSnippets\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return \Bss\RichSnippets\Helper\ProductHelper
     */
    public function getProductHelper()
    {
        return $this->productHelper;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->context->getStoreManager()->getStore()->getId();
    }

    /**
     * Get Current url
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentUrl()
    {
        return $this->context->getStoreManager()->getStore()->getCurrentUrl();
    }

    /**
     * @param object $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductCategoryPath($product)
    {
        $allCategory = $this->getAllCategory($product);
        return $this->getCanonicalUrlPath($product, $allCategory);
    }

    /**
     * @param object $product
     * @return object|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllCategory($product)
    {
        $categoryIds = $product->getCategoryIds();

        if ($categoryIds == []) {
            return null;
        }

        $categories = $this->productHelper->getCategoryCollection()
            ->addAttributeToFilter('entity_id', $categoryIds);
        $max = null;
        $myCategory = null;
        foreach ($categories as $key => $category) {
            $max = $key;
        }
        foreach ($categories as $key => $category) {
            if ($key == $max) {
                $myCategory = $category;
            }
        }
        return $myCategory;
    }

    /**
     * Get canonical url path
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getCanonicalUrlPath($product, $category = null)
    {
        return $this->productUrlPathGenerator->getUrlPath($product, $category);
    }

    /**
     * Get media url
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

    /**
     * Get base url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->context->getStoreManager()->getStore()->getBaseUrl();
    }

    /**
     * Get the result url with params message
     *
     * @return string
     */
    public function getResultUrl()
    {
        return $this->context->getUrlBuilder()->getUrl(
            'catalogsearch/result'
        );
    }

    /**
     * Get currency code
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCode()
    {
        return $this->context->getStoreManager()->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Get current product
     *
     * @return Product
     */
    public function getCurrentProduct()
    {
        $currentProduct = $this->registry->registry('current_product');
        return $currentProduct;
    }

    /**
     * Get short title
     *
     * @return mixed
     */
    public function getTitlePage()
    {
        return $this->pageTitle->getShort();
    }

    /**
     * Get layout
     *
     * @return mixed
     */
    public function getLayoutFactory()
    {
        return $this->context->getPageConfig();
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
     * @param object $currentProduct
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFinalUrl($currentProduct)
    {
        $isUseCategory = $this->helper->isSetFlag(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_ADD_CATEGORY);
        $productSuffix = $this->helper->getConfig(
            \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX
        );
        $currentUrl = $this->getBaseUrl();
        if ($isUseCategory) {
            $productPath = $this->getProductCategoryPath($currentProduct);
        } else {
            $productPath = $this->getCanonicalUrlPath($currentProduct);
        }

        $finalUrl = $currentUrl . $productPath . $productSuffix;
        return $finalUrl;
    }

    /**
     * @return bool|false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDataJsonProduct()
    {
        $helper = $this->getHelper();
        $currentProduct = $this->getCurrentProduct();
        $sku = $currentProduct->getSku();
        $name = $currentProduct->getName();
        $price = $currentProduct->getFinalPrice();

        $layoutFactory = $this->getLayoutFactory();

        if (isset($layoutFactory->getMetadata()['description'])) {
            $description = $layoutFactory->getMetadata()['description'];
        } else {
            $description = $currentProduct->getMetaDescription();
        }

        $description = strip_tags($description);
        $description = str_replace('"', '\'', $description);

        $productStock = $this->getProductHelper()->getStockItem($currentProduct);
        $imageProduct = $this->getUrl('pub/media/catalog') . 'product' . $currentProduct->getImage();
        $currentCurrencyCode = $this->getCode();

        $isShowConditions = $helper->isSetFlag(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_CONDITION);
        $isUseCategory = $helper->isSetFlag(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_ADD_CATEGORY);
        $productSuffix = $helper->getConfig(
            \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX
        );
        $currentUrl = $this->getBaseUrl();
        if ($isUseCategory) {
            $productPath = $this->getProductCategoryPath($currentProduct);
        } else {
            $productPath = $this->getCanonicalUrlPath($currentProduct);
        }

        $finalUrl = $currentUrl . $productPath . $productSuffix;

        $richSnippetsProduct = $this->getRichSnippetsProduct(
            $name,
            $imageProduct,
            $description,
            $currentProduct
        );
        $richSnippetsProduct = $this->processRichSnippetsProduct(
            $richSnippetsProduct,
            $helper,
            $sku,
            $currentProduct
        );

        $richSnippetsProduct = $this->processRichSnippetPrice($richSnippetsProduct, $helper, $currentProduct);

        $enablePrice = $helper->isSetFlag(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_PRICE);
        $enableInStock = $helper->isSetFlag(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_AVAILABILITY);
        $isNewProduct = $this->getProductHelper()->isNew($currentProduct);
        if ($enablePrice) {
            $dataPrice = $this->getDataPrice(
                $currentCurrencyCode,
                $finalUrl,
                $price,
                $productStock,
                $enableInStock,
                $isNewProduct,
                $isShowConditions
            );
            $richSnippetsProduct['offers'] = $dataPrice;
        } else {
            $richSnippetsProduct['offers'] = [
                "@type" => "Offer"
            ];
        }

        $dataJsonProduct = $helper->jsonEncode($richSnippetsProduct);
        return $dataJsonProduct;
    }

    /**
     * @param array $richSnippetsProduct
     * @param object $helper
     * @param object $currentProduct
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processRichSnippetPrice($richSnippetsProduct, $helper, $currentProduct)
    {
        $enableRating = $helper->isSetFlag(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_RATING);
        $rating = $this->getProductHelper()->getRatingSummary($currentProduct->getId(), $this->getStoreId());
        if ($enableRating &&
            isset($rating['count']) &&
            $rating['count']) {
            $richSnippetsProduct['aggregateRating'] = [
                "@type" => "AggregateRating",
                "ratingValue" => $this->escapeHtml($rating['value']),
                "bestRating" => "100",
                "ratingCount" => $this->escapeHtml($rating['count'])
            ];
        }
        $enableReview = $helper->isSetFlag(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_REVIEW);
        $richItem = $this->getProductHelper()->getReviewsCollection($currentProduct->getId(), $this->getStoreId())->getItems();
        if ($enableReview && $richItem && !empty($richItem)) {
            $dataReview = $this->getDataReview($richItem);
            $richSnippetsProduct['review'] = $dataReview;
        }
        return $richSnippetsProduct;
    }
    /**
     * @param array $richSnippetsProduct
     * @param object $helper
     * @param string $sku
     * @param object $currentProduct
     * @return mixed
     */
    public function processRichSnippetsProduct($richSnippetsProduct, $helper, $sku, $currentProduct)
    {
        $enableSku = $helper->isSetFlag(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_SKU);
        if ($enableSku) {
            $richSnippetsProduct['sku'] = $this->escapeHtml($sku);
        }

        $gtinAttribute = $helper->getConfig(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_GTIN);
        if ($gtinAttribute) {
            $gtinValue = $currentProduct->getData($gtinAttribute);
            if ($gtinValue) {
                $gtinValue = $this->getOptionTextValue($gtinValue, $gtinAttribute);
                $richSnippetsProduct['mpn'] = $this->escapeHtml($gtinValue);
            }
        }

        $customAttribute = $helper->getProductCustomProperties();
        if ($customAttribute && !empty($customAttribute)) {
            foreach ($customAttribute as $arrayValue) {
                $customOptionValue = $this->getOptionTextValue(
                    $currentProduct->getData($arrayValue['attribute']),
                    $arrayValue['attribute']
                );
                $richSnippetsProduct[$arrayValue['property_name']] = $this->escapeHtml($customOptionValue);
            }
        }
        return $richSnippetsProduct;
    }

    /**
     * @param string $name
     * @param string $imageProduct
     * @param string $description
     * @param object $currentProduct
     * @return array
     */
    public function getRichSnippetsProduct(
        $name,
        $imageProduct,
        $description,
        $currentProduct
    ) {
        $helper = $this->getHelper();
        $richSnippetsProduct = [
            "@context" => "http://schema.org/",
            "@type" => "Product"
        ];
        $richSnippetsProduct['name'] = $this->escapeHtml($name);

        //Rich Snippets Image
        $enableImage = $helper->isSetFlag(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_IMAGE);
        if ($enableImage) {
            $richSnippetsProduct['image'] = $this->escapeUrl($imageProduct);
        }
        //Rich Snippets Description
        $enableDescription = $helper->isSetFlag(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_DESCRIPTION);
        if ($enableDescription) {
            $richSnippetsProduct['description'] = $this->escapeHtml($description);
        }

        $brandAttribute = $helper->getConfig(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_ADD_BRAND);
        if ($brandAttribute) {
            //Get Product Attribute
            $brandValue = $currentProduct->getData($brandAttribute);
            if ($brandValue) {
                $brandValue = $this->getOptionTextValue($brandValue, $brandAttribute);
                $richSnippetsProduct['brand'] = [
                    "@type" => "Brand",
                    "name" => $this->escapeHtml($brandValue)
                ];
            }
        }
        return $richSnippetsProduct;
    }

    /**
     * Check if attribute has options
     * @param string $optionValue
     * @param string $optionAttribute
     * @return string
     */
    protected function getOptionTextValue($optionValue, $optionAttribute)
    {
        try {
            if (is_string($optionValue) && is_string($optionAttribute)) {
                $attr = $this->productAttributeRepository->get($optionAttribute);
                $types = ['boolean', 'select'];
                if (in_array($attr->getFrontendInput(), $types)) {
                    foreach ($attr->getSource()->getAllOptions() as $option) {
                        if ($option['value'] == $optionValue) {
                            return $option['label'];
                        }
                    }
                } elseif ($attr->getFrontendInput() == 'multiselect') {
                    return $this->seoCoreHelper->implode(', ', $attr->getSource()->getOptionText($optionValue));
                }
            }
        } catch (\Exception $exception) {
            return $this->sanitizeOptionValue($optionValue);
        }
        return $this->sanitizeOptionValue($optionValue);
    }

    /**
     * @param string|object $optionValue
     * @return string
     */
    protected function sanitizeOptionValue($optionValue)
    {
        if (is_string($optionValue)) {
            return $optionValue;
        }
        if (!$optionValue || is_object($optionValue) || is_array($optionValue)) {
            return '';
        }
        if (is_array($optionValue)) {
            $optionValue = implode(',', $optionValue);
        }
        return (string) $optionValue;
    }

    /**
     * @param string $currentCurrencyCode
     * @param string $finalUrl
     * @param int $price
     * @param object $productStock
     * @param bool $enableInStock
     * @param bool $isNewProduct
     * @param bool $isShowConditions
     * @return array
     */
    public function getDataPrice(
        $currentCurrencyCode,
        $finalUrl,
        $price,
        $productStock,
        $enableInStock,
        $isNewProduct,
        $isShowConditions
    ) {
        $dataPrice = [
            "@type" => "Offer",
            "priceCurrency" => $this->escapeHtml($currentCurrencyCode),
            "url" => $this->escapeUrl($finalUrl),
            "price" => $this->escapeHtml($price)
        ];

        if ($productStock->getIsInStock() && $enableInStock) {
            $dataPrice['availability'] = "http://schema.org/InStock";
        }
        if (!$productStock->getIsInStock() == '1' && $enableInStock) {
            $dataPrice['availability'] = "http://schema.org/OutOfStock";
        }

        if ($isNewProduct && $isShowConditions) {
            $dataPrice['itemCondition'] = "https://schema.org/NewCondition";
        }
        return $dataPrice;
    }
    /**
     * @param array $richItem
     * @return array
     */
    public function getDataReview($richItem)
    {
        $dataReview = [];
        foreach ($richItem as $review) {
            $detailReview = $review->getDetail();
            $detailReview = strip_tags($detailReview);
            $detailReview = str_replace('"', '\'', $detailReview);

            $reviewItemToAdd = [
                "@type" => "Review",
                "author" => $this->escapeHtml($review->getNickname()),
                "datePublished" => $this->escapeHtml($review->getCreatedAt()),
                "description" => $this->escapeHtml($detailReview)
            ];
            $dataReview[] = $reviewItemToAdd;
        }
        return $dataReview;
    }

    /**
     * Get current category
     *
     * @return mixed
     */
    public function getCurrentCategoryOb()
    {
        return $this->registry->registry('current_category');
    }
}
