<?php

namespace Intcomex\CredomaticMSI\Model;

use Magento\Framework\Data\OptionSourceInterface;

class CuotasOptions implements OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            ['value' =>  18, 'label' => __('18 Cuotas')],
            ['value' =>  24, 'label' => __('24 Cuotas')]
        ];
    }
}
