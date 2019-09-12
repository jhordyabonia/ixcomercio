<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Block\Adminhtml\Ticket\Edit\Tab;

/**
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class OPtions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Ui\Component\Layout\Tabs\TabInterface
{

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isAjaxLoaded()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Add Options');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Add Options');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post'
            ]
        ]);
        $formName = "zendesk_ticket_form";
        /**  new Order Fieldset @var $fieldset */
        $fieldset = $form->addFieldset(
            'general',
            ['legend' => __('Order: ')]
        );
        $field = $fieldset->addField(
            'order_id',
            'label',
            [
                'name' => 'order_id',
                'label' => __('Select Order'),
                'class' => 'widget-option',
                'required' => true,
                'note' => __(''),
                'data-form-part' => $formName
            ]
        );
        $typeButton = \Wagento\Zendesk\Block\Adminhtml\Page\Widget\Order\Chooser::class;
        $messageButton = __('Select Order...');
        $this->addButtonModal($fieldset->getId(), $messageButton, $field, $typeButton);

        /**  new Customer Fieldset @var $fieldset */
        $fieldsetCustomer = $form->addFieldset(
            'general_customer',
            ['legend' => __('Customer: ')]
        );
        $fieldCustomer = $fieldsetCustomer->addField(
            'customer_email',
            'label',
            [
                'name' => 'customer_email',
                'label' => __('Select Customer'),
                'class' => 'widget-option',
                'required' => false,
                'note' => __(''),
                'data-form-part' => $formName
            ]
        );

        $typeButton = \Wagento\Zendesk\Block\Adminhtml\Page\Widget\Customer\Chooser::class;
        $messageButton = __('Select Customer...');
        $this->addButtonModal($fieldsetCustomer->getId(), $messageButton, $fieldCustomer, $typeButton);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Add the Button which opens a modal
     * @param int $fieldsetId
     * @param string $buttonMessage
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $field
     * @param $type
     * @return void
     */
    protected function addButtonModal($fieldsetId, $buttonMessage, $field, $type)
    {
        $dataButton = ['button' => ['open' => $buttonMessage],
            'type' => $type];

        $helperBlock = $this->getLayout()->createBlock(
            $dataButton["type"],
            '',
            ['data' => $dataButton]
        );
        if ($helperBlock instanceof \Magento\Framework\DataObject) {
            $helperBlock->setConfig(
                $dataButton
            )->setFieldsetId(
                $fieldsetId
            )->prepareElementHtml(
                $field
            );
        }
    }
}
