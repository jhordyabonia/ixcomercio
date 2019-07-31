<?php
/**
 * Pasarela_Grid Add New Row Form Admin Block.
 * @category    Pasarela
 * @package     Pasarela_Grid
 * @author      Pasarela Software Private Limited
 *
 */
namespace Pasarela\Grid\Block\Adminhtml\Grid\Edit;

/**
 * Adminhtml Add New Row Form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context,
     * @param \Magento\Framework\Registry $registry,
     * @param \Magento\Framework\Data\FormFactory $formFactory,
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
     * @param \Pasarela\Grid\Model\Status $options,
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Pasarela\Grid\Model\Status $options,
        array $data = []
    ) {
        $this->_options = $options;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $model = $this->_coreRegistry->registry('row_data');
        $form = $this->_formFactory->create(
            ['data' => [
                            'id' => 'edit_form',
                            'enctype' => 'multipart/form-data',
                            'action' => $this->getData('action'),
                            'method' => 'post'
                        ]
            ]
        );

        $form->setHtmlIdPrefix('trax_match');
        if ($model->getEntityId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Row Data'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Row Data'), 'class' => 'fieldset-wide']
            );
        }

        $fieldset->addField(
            'payment_type',
            'text',
            [
                'name' => 'payment_type',
                'label' => __('tipo de Pago'),
                'id' => 'payment_type',
                'title' => __('Tipo de Pago'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'gateway',
            'text',
            [
                'name' => 'gateway',
                'label' => __('Gateway'),
                'id' => 'gateway',
                'title' => __('Gateway'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'payment_code',
            'text',
            [
                'name' => 'payment_code',
                'label' => __('Código de Pago'),
                'id' => 'payment_code',
                'title' => __('Código de Pago'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'trax_code',
            'text',
            [
                'name' => 'trax_code',
                'label' => __('Código Pasarela'),
                'id' => 'trax_code',
                'title' => __('Código Pasarela'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'country_code',
            'text',
            [
                'name' => 'country_code',
                'label' => __('Código País'),
                'id' => 'country_code',
                'title' => __('Código País'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'store_code',
            'text',
            [
                'name' => 'store_code',
                'label' => __('Código Tienda'),
                'id' => 'store_code',
                'title' => __('Código Tienda'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
