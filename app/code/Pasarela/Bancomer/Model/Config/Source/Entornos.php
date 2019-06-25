<?php

namespace Pasarela\Bancomer\Model\Config\Source;

class Entornos implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Desarrollo')],
            ['value' => 1, 'label' => __('Produccion')],
        ];
    }
}