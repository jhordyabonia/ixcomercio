<?php

namespace Intcomex\CredomaticMSI\Block\Adminhtml\Campaigndetail\Edit;

use Intcomex\CredomaticMSI\Model\CampaignOptions;
use Intcomex\CredomaticMSI\Model\CuotasOptions;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    private $cuotasOptions;
    private $_options;
    private $campaignOptions;

    /**
     * @param \Magento\Backend\Block\Template\Context $context,
     * @param \Magento\Framework\Registry $registry,
     * @param \Magento\Framework\Data\FormFactory $formFactory,
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
     * @param \Intcomex\CredomaticMSI\Model\Status $options,
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Intcomex\CredomaticMSI\Model\Status $options,
        CampaignOptions $campaignOptions,
        CuotasOptions $cuotasOptions,
        array $data = []
    ) {
        $this->_options = $options;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->cuotasOptions = $cuotasOptions;
        $this->campaignOptions = $campaignOptions;
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

        $form->setHtmlIdPrefix('Credomatic_');
        if ($model->getId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Item'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Item'), 'class' => 'fieldset-wide']
            );
        }

        $fieldset->addField(
            'campaign_id',
            'select',
            [
                'name' => 'campaign_id',
                'label' => __('Campaign'),
                'id' => 'campaign_id',
                'title' => __('Campaign'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->campaignOptions->toOptionArray()
            ]
        );

        $fieldset->addField(
            'sku',
            'text',
            [
                'name' => 'sku',
                'label' => __('SKU'),
                'id' => 'sku',
                'title' => __('SKU'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'fee',
            'select',
            [
                'name' => 'fee',
                'label' => __('Fee'),
                'id' => 'fee',
                'title' => __('Fee'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->cuotasOptions->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'max_units',
            'text',
            [
                'name' => 'max_units',
                'label' => __('Max units'),
                'id' => 'max_units',
                'title' => __('Max units'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'id' => 'status',
                'title' => __('Status'),
                'class' => 'select',
                'required' => true,
                'values' => $this->_options->toOptionArray(),
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
