<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Model\Config\Source\Customer;

class OrderNumberLimit implements \Magento\Framework\Data\OptionSourceInterface
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
            ['label' => '1', 'value' => '1'],
            ['label' => '2', 'value' => '2'],
            ['label' => '3', 'value' => '3'],
            ['label' => '5', 'value' => '5'],
            ['label' => '8', 'value' => '8'],
            ['label' => '13', 'value' => '13'],
            ['label' => 'All', 'value' => ''],
        ];

        return $this->options;
    }
}
