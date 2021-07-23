<?php

namespace Intcomex\SalesRulesPaymentMethod\Plugin;

use Magento\SalesRule\Model\Rule\Condition\Address;

class AddPaymentMethodOptionBack
{
    /**
     * @param Address $subject
     * @param $result
     * @return Address
     */
    public function afterLoadAttributeOptions(Address $subject, $result)
    {
        $attributeOption = $subject->getAttributeOption();
        $attributeOption['payment_method'] = __('Payment Method');

        $subject->setAttributeOption($attributeOption);

        return $subject;
    }
}