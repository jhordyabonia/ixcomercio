<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Model\Config\Source\Ticket;

class Priority implements \Magento\Framework\Data\OptionSourceInterface
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
            ['label' => '-', 'value' => ''],
            ['label' => 'Low', 'value' => 'low'],
            ['label' => 'Normal', 'value' => 'normal'],
            ['label' => 'High', 'value' => 'high'],
            ['label' => 'Urgent', 'value' => 'urgent']
        ];

        return $this->options;
    }
}
