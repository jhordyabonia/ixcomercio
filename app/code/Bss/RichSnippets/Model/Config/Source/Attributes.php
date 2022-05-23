<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RichSnippets
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RichSnippets\Model\Config\Source;

/**
 * Class BusinessType
 * @package Bss\RichSnippets\Model\Config\Source
 */
class Attributes
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $attributeFactory;

    /**
     * Attributes constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
    }
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $attributes = $this->attributeFactory->create()->getItems();
        $dataReturn = [];
        $dataReturn[] = [
            'value' => '',
            'label' => '   '
        ];
        foreach ($attributes as $attributes) {
            $dataReturn[] = [
                'value' => $attributes->getAttributeCode(),
                'label' => $attributes->getAttributeCode()
            ];
        }
        return $dataReturn;
    }
}
