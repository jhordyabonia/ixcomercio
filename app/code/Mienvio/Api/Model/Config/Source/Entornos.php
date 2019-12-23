<?php

namespace Mienvio\Api\Model\Config\Source;

class Entornos implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Staging')],
            ['value' => 1, 'label' => __('Produccion')],
        ];
    }
}