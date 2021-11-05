<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Shopping Cart Rule data model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Intcomex\CustomLog\Rewrite;

use Magento\SalesRule\Model\Validator;

/**
 * @api
 * @since 100.0.2
 */
class Calculator extends Validator
{
    /**
     * Quote item free shipping ability check
     * This process not affect information about applied rules, coupon code etc.
     * This information will be added during discount amounts processing
     *
     * @param   \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return  \Magento\OfflineShipping\Model\SalesRule\Calculator
     */
    public function processFreeShipping(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
$quoteId = $item->getQuote()->getId();
$class = 'Magento\OfflineShipping\Model\SalesRule\Calculator';
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/FreeShipping.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$this->_logger = $logger;
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'BeforeSet processFreeShipping() $item->getFreeShipping():: ' . $item->getFreeShipping());
        $address = $item->getAddress();
        $item->setFreeShipping(false);
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'AfterSet processFreeShipping() $item->getFreeShipping():: ' . $item->getFreeShipping());
        foreach ($this->_getRules($address) as $rule) {
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'processFreeShipping() $rule->getId():: ' . $rule->getId());
            /* @var $rule \Magento\SalesRule\Model\Rule */
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'processFreeShipping() if (!$this->validatorUtility->canProcessRule($rule, $address))');
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'processFreeShipping() if (!$rule->getActions()->validate($item))');
                continue;
            }

            switch ($rule->getSimpleFreeShipping()) {
                case Rule::FREE_SHIPPING_ITEM:
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'processFreeShipping() FREE_SHIPPING_ITEM');
                    $item->setFreeShipping($rule->getDiscountQty() ? $rule->getDiscountQty() : true);
                    break;

                case Rule::FREE_SHIPPING_ADDRESS:
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'processFreeShipping() FREE_SHIPPING_ADDRESS');
                    $address->setFreeShipping(true);
                    break;
            }
            if ($rule->getStopRulesProcessing()) {
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'processFreeShipping() STOP RULES PROCESSING');
                break;
            }
        }
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'processFreeShipping() $item->getFreeShipping()::' . $item->getFreeShipping());
$this->_logger->debug("QuoteId:: $quoteId Class:: $class Method:: " . 'processFreeShipping() $address->getFreeShipping():: ' . $address->getFreeShipping());
        return $this;
    }
}
