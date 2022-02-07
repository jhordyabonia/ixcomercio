<?php

namespace Intcomex\ImportProducts\Plugin\Model\Import\Config;

class Converter
{
    /**
     * Convert dom node tree to array.
     *
     * @param \Magento\ImportExport\Model\Import\Config\Converter $subject
     * @param $result
     * @return array
     */
    public function afterConvert(\Magento\ImportExport\Model\Import\Config\Converter $subject, $result): array
    {
        if (isset($result['entities']['catalog_product'])) {
            $result['entities']['catalog_product']['model'] = \Intcomex\ImportProducts\Model\Import\Product::class;
        }
        return $result;
    }
}
