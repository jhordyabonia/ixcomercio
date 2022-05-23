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
namespace Bss\RichSnippets\Block\System\Form\Field;

/**
 * Class Country
 *
 * @package Bss\HrefLang\Block\System\Form\Field
 */
class Attributes extends \Magento\Framework\View\Element\Html\Select
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $attributeFactory;

    /**
     * Attributes constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeFactory,
        array $data = []
    ) {
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $attributes = $this->attributeFactory->create()->getItems();
        if (!$this->getOptions()) {
            foreach ($attributes as $itemObject) {
                $this->addOption($itemObject->getAttributeCode(), $itemObject->getAttributeCode());
            }
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
