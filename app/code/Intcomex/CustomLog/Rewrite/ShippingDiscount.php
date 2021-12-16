<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Intcomex\CustomLog\Rewrite;

use Magento\Quote\Api\Data\ShippingAssignmentInterface as ShippingAssignment;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\SalesRule\Model\Quote\Discount as DiscountCollector;
use Magento\SalesRule\Model\Validator;

/**
 * Total collector for shipping discounts.
 */
class ShippingDiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var Validator
     */
    private $calculator;

    /**
     * @param Validator $calculator
     */
    public function __construct(Validator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     *
     * @param Quote $quote
     * @param ShippingAssignment $shippingAssignment
     * @param Total $total
     * @return ShippingDiscount
     */
    public function collect(Quote $quote, ShippingAssignment $shippingAssignment, Total $total): self
    {
$quoteId = $quote->getId();
$class = 'Magento\SalesRule\Model\Quote\Address\Total\ShippingDiscount';
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/FreeShipping.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$this->_logger = $logger;
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() 1');
        parent::collect($quote, $shippingAssignment, $total);
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() 2');
        $address = $shippingAssignment->getShipping()->getAddress();
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() 3');
        $this->calculator->reset($address);
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() 4');
        $items = $shippingAssignment->getItems();
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() 5');
        if (!count($items)) {
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() if (!count($items))');
            return $this;
        }

        $address->setShippingDiscountAmount(0);
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() $address->getShippingDiscountAmount() ' . $address->getShippingDiscountAmount());
        $address->setBaseShippingDiscountAmount(0);
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() $address->getBaseShippingDiscountAmount() ' . $address->getBaseShippingDiscountAmount());

$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() $address->getShippingAmount():: ' . $address->getShippingAmount());
        if ($address->getShippingAmount()) {
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() if ($address->getShippingAmount())');
            $this->calculator->processShippingAmount($address);
            $total->addTotalAmount(DiscountCollector::COLLECTOR_TYPE_CODE, -$address->getShippingDiscountAmount());
            $total->addBaseTotalAmount(
                DiscountCollector::COLLECTOR_TYPE_CODE,
                -$address->getBaseShippingDiscountAmount()
            );
            $total->setShippingDiscountAmount($address->getShippingDiscountAmount());
            $total->setBaseShippingDiscountAmount($address->getBaseShippingDiscountAmount());

            $this->calculator->prepareDescription($address);
            $total->setDiscountDescription($address->getDiscountDescription());
            $total->setSubtotalWithDiscount($total->getSubtotal() + $total->getDiscountAmount());
            $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $total->getBaseDiscountAmount());

            $address->setDiscountAmount($total->getDiscountAmount());
            $address->setBaseDiscountAmount($total->getBaseDiscountAmount());
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() $total->getDiscountAmount():: ' . $total->getDiscountAmount());
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() $total->getBaseDiscountAmount():: ' . $total->getBaseDiscountAmount());
        }
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'collect() Finish');
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Total $total): array
    {
        $result = [];
        $amount = $total->getDiscountAmount();
        if ($amount != 0) {
            $description = $total->getDiscountDescription() ?: '';
            $result = [
                'code' => DiscountCollector::COLLECTOR_TYPE_CODE,
                'title' => strlen($description) ? __('Discount (%1)', $description) : __('Discount'),
                'value' => $amount
            ];
        }
        return $result;
    }
}
