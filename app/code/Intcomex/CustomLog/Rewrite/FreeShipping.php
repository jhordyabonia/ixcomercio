<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Intcomex\CustomLog\Rewrite;

use Magento\Quote\Model\Quote\Address\FreeShippingInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\StoreManagerInterface;

class FreeShipping implements FreeShippingInterface
{
    /**
     * @var \Intcomex\CustomLog\Rewrite\Calculator
     */
    protected $calculator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param \Intcomex\CustomLog\Rewrite\Calculator $calculator
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Intcomex\CustomLog\Rewrite\Calculator $calculator
    ) {
        $this->storeManager = $storeManager;
        $this->calculator = $calculator;
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/FreeShipping.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$this->_logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function isFreeShipping(\Magento\Quote\Model\Quote $quote, $items)
    {
$quoteId = $quote->getId();
$class = 'Magento\OfflineShipping\Model\Quote\Address\FreeShipping';
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() 1');
        /** @var \Magento\Quote\Api\Data\CartItemInterface[] $items */
        if (!count($items)) {
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() if (!count($items))');
            return false;
        }

        $result = false;
        $addressFreeShipping = true;
        $store = $this->storeManager->getStore($quote->getStoreId());
        $this->calculator->init(
            $store->getWebsiteId(),
            $quote->getCustomerGroupId(),
            $quote->getCouponCode()
        );
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setFreeShipping(0);
        /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
        foreach ($items as $item) {
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() $item->getId():: ' . $item->getId());
            if ($item->getNoDiscount()) {
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() if ($item->getNoDiscount())');
                $addressFreeShipping = false;
                $item->setFreeShipping(false);
                continue;
            }
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() 2');
            /** Child item discount we calculate for parent */
            if ($item->getParentItemId()) {
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() if ($item->getParentItemId())');
                continue;
            }
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() Before $this->calculator->processFreeShipping($item)');
            $this->calculator->processFreeShipping($item);
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() After $this->calculator->processFreeShipping($item)');
            // at least one item matches to the rule and the rule mode is not a strict
            if ((bool)$item->getAddress()->getFreeShipping()) {
                $result = true;
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() if ((bool)$item->getAddress()->getFreeShipping()) $result = true');
                break;
            }

            $itemFreeShipping = (bool)$item->getFreeShipping();
            $addressFreeShipping = $addressFreeShipping && $itemFreeShipping;
            $result = $addressFreeShipping;
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() 3');
        }
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() 4');
        $shippingAddress->setFreeShipping((int)$result);
        $this->applyToItems($items, $result);
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'isFreeShipping() $result:: ' . $result);
        return $result;
    }

    /**
     * @param AbstractItem $item
     * @param bool $isFreeShipping
     * @return void
     */
    protected function applyToChildren(\Magento\Quote\Model\Quote\Item\AbstractItem $item, $isFreeShipping)
    {
        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            foreach ($item->getChildren() as $child) {
                $this->calculator->processFreeShipping($child);
                if ($isFreeShipping) {
                    $child->setFreeShipping($isFreeShipping);
                }
            }
        }
    }

    /**
     * Sets free shipping availability to the quote items.
     *
     * @param array $items
     * @param bool $freeShipping
     */
    private function applyToItems(array $items, bool $freeShipping)
    {
        /** @var AbstractItem $item */
        foreach ($items as $item) {
            $item->getAddress()
                ->setFreeShipping((int)$freeShipping);
            $this->applyToChildren($item, $freeShipping);
        }
    }
}
