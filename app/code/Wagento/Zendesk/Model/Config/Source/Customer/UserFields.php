<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Model\Config\Source\Customer;

class UserFields implements \Magento\Framework\Option\ArrayInterface
{

    protected $options;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->options = [
            ['value' => 'id', 'label' => 'Customer Id'],
            ['value' => 'group', 'label' => 'Customer Group'],
            ['value' => 'lifetime_sale', 'label' => 'Customer Lifetime Sale'],
            ['value' => 'average_sale', 'label' => 'Customer Average Sale'],
            ['value' => 'logged_in', 'label' => 'Customer Last Logged In'],
        ];

        return $this->options;
    }
}
