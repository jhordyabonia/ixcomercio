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

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Bss\RichSnippets\Helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    public $urlInterface;

    /**
     * @var string
     */
    public $scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

    const BSS_RICH_GENERAL_ENABLE = 'bss_richsnippets/general/enable';
    const BSS_RICH_SITE_SEARCH_BOX = 'bss_richsnippets/site_structure/search_box';
    const BSS_RICH_LOCAL_TYPE = 'bss_richsnippets/local_business/local_type';
    const BSS_RICH_ENABLE_COMPANY = 'bss_richsnippets/local_business/enable';
    const RICH_PRODUCT_CONDITION = 'bss_richsnippets/product/condition';
    const RICH_PRODUCT_ADD_CATEGORY = 'bss_richsnippets/product/add_category';
    const RICH_PRODUCT_IMAGE = 'bss_richsnippets/product/image';
    const RICH_PRODUCT_DESCRIPTION = 'bss_richsnippets/product/description';
    const RICH_PRODUCT_ADD_BRAND = 'bss_richsnippets/product/add_brand';
    const RICH_PRODUCT_SKU = 'bss_richsnippets/product/sku';
    const RICH_PRODUCT_GTIN = 'bss_richsnippets/product/add_gtin';
    const RICH_PRODUCT_RATING = 'bss_richsnippets/product/rating';
    const RICH_PRODUCT_REVIEW = 'bss_richsnippets/product/review';
    const RICH_PRODUCT_PRICE = 'bss_richsnippets/product/price';
    const RICH_PRODUCT_AVAILABILITY = 'bss_richsnippets/product/availability';
    const RICH_CATEGORY_PRODUCT_OFFERS = 'bss_richsnippets/category/product_offers';
    const MAGENTO_VERSION_220 = 2;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        parent::__construct($context);
        $this->imageHelper = $imageHelper;
        $this->jsonHelper = $jsonHelper;
        $this->productMetadata = $productMetadata;
        $this->scopeConfig = $context->getScopeConfig();
        $this->urlInterface = $context->getUrlBuilder();
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getConfig($path)
    {
        $result = $this->scopeConfig->getValue($path, $this->scopeStore);
        return $result;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isSetFlag($path)
    {
        $result = $this->scopeConfig->isSetFlag($path, $this->scopeStore);
        return $result;
    }

    /**
     * @param array $dataToEncode
     * @return bool|false|string
     */
    public function jsonEncode(array $dataToEncode)
    {
        $encodedData = $this->jsonHelper->serialize($dataToEncode);
        return $encodedData;
    }

    /**
     * @return array|bool|float|int|mixed|string|null
     */
    public function getProductCustomProperties()
    {
        $version = $this->productMetadata->getVersion();
        $versionArray = explode(".", $version);

        $data = $this->scopeConfig->getValue(
            'bss_richsnippets/product/custom_properties',
            ScopeInterface::SCOPE_STORE
        );

        if ($data == null || $data == '') {
            return null;
        }

        $additionalData = '';
        if ($versionArray[1] < self::MAGENTO_VERSION_220) {
            /* For magento version below 2.2.0, using php function. */
            $additionalData = unserialize($data);
        } else {
            try {
                $additionalData = $this->jsonHelper->unserialize($data);
            } catch (\Exception $exception) {
                $this->_logger->critical(__('Unable to unserialize value, details: ') . $exception->getMessage());
            }
        }

        return $additionalData;
    }

    /**
     * Get enable
     *
     * @return mixed
     */
    public function getEnable()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/general/enable', $this->scopeStore);
        return $result;
    }

    /**
     * Get search box
     *
     * @return mixed
     */
    public function getSearchBox()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/search_box', $this->scopeStore);
        return $result;
    }

    /**
     * Get Website Name
     *
     * @return mixed
     */
    public function getWebsiteName()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/website_name', $this->scopeStore);
        return $result;
    }

    /**
     * Get enable website
     *
     * @return mixed
     */
    public function getEnableWebsite()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/enable_site_name', $this->scopeStore);
        return $result;
    }

    /**
     * Get description
     *
     * @return mixed
     */
    public function getWebsiteDescription()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/description', $this->scopeStore);
        return $result;
    }

    /**
     * Get Image
     *
     * @return mixed
     */
    public function getWebsiteImage()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/website_image', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getFileUrl()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_logo', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getComapnyPriceRange()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/price_range', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_name', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyTelephone()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_telephone', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyEmail()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_email', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyAddress()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_address', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyStreet()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_street', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanySocial()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_social', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyCountry()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_country', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCompanyPostCode()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/local_business/company_post_code', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableName()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/product/name', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableDescription()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/product/description', $this->scopeStore);
        return $result;
    }


    /**
     * @return mixed
     */
    public function getBreadscumbs()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/breadscumbs', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getTwitterUser()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/site_structure/twitter_user', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableImageCategory()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/category/image', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableDescriptionCategory()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/category/description', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableNameCategory()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/category/name', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForWebsite()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/open_graph/for_website', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForProduct()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/open_graph/for_product', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForCategory()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/open_graph/for_category', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForWebsiteT()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/twitter_card/for_website', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForProductT()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/twitter_card/for_product', $this->scopeStore);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnableForCategoryT()
    {
        $result = $this->scopeConfig->getValue('bss_richsnippets/twitter_card/for_category', $this->scopeStore);
        return $result;
    }

    /**
     * @param array $richSnippetsData
     * @param string $ratingValue
     * @param string $ratingCount
     * @return mixed
     */
    public function processProductRating($richSnippetsData, $ratingValue, $ratingCount)
    {
        if (gettype($ratingValue) === 'string' && trim($ratingValue) !== '') {
            $richSnippetsData['aggregateRating'] =  [
                "@type" => "AggregateRating",
                "ratingValue" => $ratingValue,
                "bestRating" => "100",
                "ratingCount" => $ratingCount
            ];
        }
        return $richSnippetsData;
    }

    /**
     * @param object $product
     * @return string
     */
    public function getProductImage($product)
    {
        $imageUrl = $this->imageHelper->init($product, 'product_base_image')
            ->setImageFile($product->getFile())->getUrl();
        return $imageUrl;
    }
}
