<?php

namespace Intcomex\SalesRulesPaymentMethod\Model\Rule\Condition\FilterTextGenerator\Address;

use Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address\PaymentMethod as BasePaymentMethod;

class PaymentMethod extends BasePaymentMethod
{
    /**
     * @param \Magento\Framework\DataObject $quoteAddress
     * @return string[]
     */
    public function generateFilterText(\Magento\Framework\DataObject $quoteAddress)
    {
        $filterText = [];
        if ($quoteAddress instanceof \Magento\Quote\Model\Quote\Address) {
            $value = $quoteAddress->getQuote()->getPayment()->getMethod();
            if (is_scalar($value)) {
                $filterText[] = $this->getFilterTextPrefix() . $this->attribute . ':' . $value;
            }
        }

        return $filterText;
    }
}