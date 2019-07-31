<?php
/**
 * Trax_Grid Add New Row Form Admin Block.
 * @category    Trax
 * @package     Trax_Grid
 * @author      Trax Software Private Limited
 *
 */
namespace Trax\Grid\Block\Adminhtml\Grid\Edit;

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
     * @param \Trax\Grid\Model\Status $options,
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Trax\Grid\Model\Status $options,
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

        $form->setHtmlIdPrefix('trax_');
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
            'carrier',
            'text',
            [
                'name' => 'carrier',
                'label' => __('Carrier'),
                'id' => 'carrier',
                'title' => __('Carrier'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'service_type',
            'text',
            [
                'name' => 'service_type',
                'label' => __('Tipo de Servicio'),
                'id' => 'service_type',
                'title' => __('Tipo de Servicio'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'trax_code',
            'text',
            [
                'name' => 'trax_code',
                'label' => __('Código Trax'),
                'id' => 'trax_code',
                'title' => __('Código Trax'),
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
