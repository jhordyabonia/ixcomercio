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
 * Class Regex
 * @package Bss\RichSnippets\Block\System\Form\Field
 */
class Regex extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = [];

    /**
     * @var object
     */
    protected $attributesRender;

    /**
     * Enable the "Add after" button or not
     *
     * @var bool
     */
    protected $_addAfter = true;

    /**
     * @var $_addButtonLabel
     */
    protected $_addButtonLabel;

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Get country render html
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributesRender()
    {
        if (!$this->attributesRender) {
            $this->attributesRender = $this->getLayout()->createBlock(
                '\Bss\RichSnippets\Block\System\Form\Field\Attributes',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->attributesRender;
    }

    /**
     * @inheritDoc
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('property_name', ['label' => __('Property Name')]);
        $this->addColumn(
            'attribute',
            [
                'label' => __('Attribute'),
                'renderer' => $this->getAttributesRender(),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @inheritDoc
     *
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $attribute = $row->getData('attribute');
        $options = [];
        $options['option_' . $this->getAttributesRender()->calcOptionHash($attribute)] = 'selected="selected"';
        $row->setData('option_extra_attrs', $options);
    }
}
