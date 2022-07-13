<?php
/**
 * Aheadworks Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://ecommerce.aheadworks.com/end-user-license-agreement/
 *
 * @package    Popup
 * @version    1.2.3
 * @copyright  Copyright (c) 2020 Aheadworks Inc. (http://www.aheadworks.com)
 * @license    https://ecommerce.aheadworks.com/end-user-license-agreement/
 */
namespace Aheadworks\Popup\Block\Adminhtml\Popup;

/**
 * Class Edit
 * @package Aheadworks\Popup\Block\Adminhtml\Popup
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_objectId = 'id';
        $this->_blockGroup = 'Aheadworks_Popup';
        $this->_controller = 'adminhtml_popup';
        $this->setId('popup_edit');
        parent::_construct();

        /* @var $model \Aheadworks\Popup\Model\Popup */
        $model = $this->coreRegistry->registry('aw_popup_model');
        if ($model && $model->getId()) {
            $this->buttonList->update('save', 'class_name', \Magento\Backend\Block\Widget\Button\SplitButton::class);
            $this->buttonList->update('save', 'options', $this->_getSaveButtonOptions());
        }
        $this->buttonList->add(
            'save_and_continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            0
        );
    }

    /**
     * Get options for save button
     *
     * @return array
     */
    protected function _getSaveButtonOptions()
    {
        return [
            [
                'label' => __('Save'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ],
                'default' => true
            ],
            [
                'label' => __('Save as New'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndNew', 'target' => '#edit_form']],
                ],
                'default' => false
            ]
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/popup/');
    }

    /**
     * Get URL for delete button
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/popup/delete', ['_current' => true]);
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = '
            require([
                "jquery",
                "mage/mage"
            ], function($){
                var $form = $("#edit_form");
                    $form.mage("form", {
                    handlersData: {
                        saveAndNew: {
                            action: {
                                args: {back: "new"}
                            }
                        },
                    }
                });
            });';
        return parent::_prepareLayout();
    }
}
