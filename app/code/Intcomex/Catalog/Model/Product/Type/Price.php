<?php

namespace Intcomex\Catalog\Model\Product\Type;

use Intcomex\Catalog\Helper\Timezone;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\Store;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Product price cache tag
     */
    const CACHE_TAG = 'PRODUCT_PRICE';

    /**
     * @var array
     */
    protected static $attributeCache = [];

    /**
     * Core event manager proxy
     *
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * Customer session
     *
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Rule factory
     *
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var ProductTierPriceInterfaceFactory
     */
    protected $tierPriceFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var ProductTierPriceExtensionFactory
     */
    private $tierPriceExtensionFactory;

    /**
     * @var Timezone
     */
    protected $_customLocaleDate;

    /**
     * Constructor
     *
     * @param RuleFactory $ruleFactory
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param ScopeConfigInterface $config
     * @param ProductTierPriceExtensionFactory|null $tierPriceExtensionFactory
     * @param Timezone $customLocaleDate
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RuleFactory $ruleFactory,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        Session $customerSession,
        ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        ProductTierPriceInterfaceFactory $tierPriceFactory,
        ScopeConfigInterface $config,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory = null,
        Timezone $customLocaleDate
    ) {
        $this->_customLocaleDate = $customLocaleDate;
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config,
            $tierPriceExtensionFactory
        );
    }

    /**
     * Calculate and apply special price
     *
     * @param float $finalPrice
     * @param float $specialPrice
     * @param string $specialPriceFrom
     * @param string $specialPriceTo
     * @param int|string|Store $store
     * @return float
     */
    public function calculateSpecialPrice(
        $finalPrice,
        $specialPrice,
        $specialPriceFrom,
        $specialPriceTo,
        $store = null
    ): float
    {
        if ($specialPrice !== null && $specialPrice != false) {
            if ($this->_customLocaleDate->isScopeDateInInterval($store, $specialPriceFrom, $specialPriceTo)) {
                $finalPrice = min($finalPrice, $specialPrice);
            }
        }
        return $finalPrice;
    }
}
