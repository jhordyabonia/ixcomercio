<?php

namespace Intcomex\Catalog\Pricing\Price;

use Intcomex\Catalog\Helper\Timezone;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\WebsiteInterface;

class SpecialPrice extends \Magento\Catalog\Pricing\Price\SpecialPrice
{
    /**
     * Price type special
     */
    const PRICE_CODE = 'special_price';

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var Timezone
     */
    protected $_customLocaleDate;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $localeDate
     * @param Timezone $customLocaleDate
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $localeDate,
        Timezone $customLocaleDate
    ) {
        $this->_customLocaleDate = $customLocaleDate;
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency, $localeDate);
    }

    /**
     * @inheritdoc
     */
    public function isScopeDateInInterval(): bool
    {
        return $this->_customLocaleDate->isScopeDateInInterval(
            WebsiteInterface::ADMIN_CODE,
            $this->getSpecialFromDate(),
            $this->getSpecialToDate()
        );
    }
}
