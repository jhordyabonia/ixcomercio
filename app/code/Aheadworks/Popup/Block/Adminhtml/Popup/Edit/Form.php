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
namespace Aheadworks\Popup\Block\Adminhtml\Popup\Edit;

use Aheadworks\Popup\Model\Source\CustomerSegments;
use Aheadworks\Popup\Model\Source\Event;
use Aheadworks\Popup\Model\Source\Position;
use Aheadworks\Popup\Model\Source\PageType;
use Aheadworks\Popup\Model\ThirdPartyModule\Manager as ModuleManager;

/**
 * Class Form
 * @package Aheadworks\Popup\Block\Adminhtml\Popup\Edit
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Wysiwyg Config
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    private $wysiwygConfig;

    /**
     * Group repository
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * Search criteria builder
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Object converter
     * @var \Magento\Framework\Convert\DataObject
     */
    private $objectConverter;

    /**
     * System store
     * @var \Magento\Store\Model\System\Store
     */
    private $systemStore;

    /**
     * Conditions
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditions;

    /**
     * Renderer fieldset factory
     * @var \Magento\Backend\Block\Widget\Form\Renderer\FieldsetFactory
     */
    private $rendererFieldsetFactory;

    /**
     * @var PageBuilderRenderer
     */
    private $pageBuilderRenderer;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var CustomerSegments
     */
    private $segmentsSource;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\FieldsetFactory $rendererFieldsetFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param PageBuilderRenderer $pageBuilderRenderer
     * @param ModuleManager $moduleManager
     * @param CustomerSegments $segmentsSource
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\FieldsetFactory $rendererFieldsetFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        PageBuilderRenderer $pageBuilderRenderer,
        ModuleManager $moduleManager,
        CustomerSegments $segmentsSource,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
        $this->conditions = $conditions;
        $this->rendererFieldsetFactory = $rendererFieldsetFactory;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->pageBuilderRenderer = $pageBuilderRenderer;
        $this->moduleManager = $moduleManager;
        $this->segmentsSource = $segmentsSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Aheadworks\Popup\Model\Popup */
        $model = $this->_coreRegistry->registry('aw_popup_model');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setUseContainer(true);
        $form->setHtmlIdPrefix('popup_');

        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $form->addFieldset(
            'general_fieldset',
            [
                'legend' => __('General Information'),
            ]
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'options' => [1 => __('Enabled'), 0 => __('Disabled')]
            ]
        );

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_ids',
                'multiselect',
                [
                    'name' => 'store_ids[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->systemStore->getStoreValuesForForm(false, true),
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_ids',
                'hidden',
                ['name' => 'store_ids[]']
            );
            $model->setStoreIds($this->_storeManager->getStore(true)->getId());
        }

        $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        $fieldset->addField(
            'customer_groups',
            'multiselect',
            [
                'name' => 'customer_groups',
                'label' => __('Customer Groups'),
                'title' => __('Customer Groups'),
                'required' => true,
                'values' => $this->objectConverter->toOptionArray($customerGroups, 'id', 'code')
            ]
        );

        if ($this->moduleManager->isCustomerSegmentationModuleEnabled()) {
            $fieldset->addField(
                'customer_segments',
                'multiselect',
                [
                    'name' => 'customer_segments',
                    'label' => __('Customer Segments'),
                    'title' => __('Customer Segments'),
                    'values' => $this->segmentsSource->toOptionArray(),
                    'note' => __('Note, segments apply to signed in customers only. Guest visitors will not see '
                        . ' popup, if any segment is selected.')
                ]
            );
        }

        if (null === $model->getData('event')) {
            $model->setData('event', Event::DEFAULT_VALUE);
        }
        /** @var Event $eventSource */
        $eventSource = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(Event::class);
        $fieldset->addField(
            'event',
            'select',
            [
                'name' => 'event',
                'label' => __('Event'),
                'title' => __('Event'),
                'options' => $eventSource->getOptionArray()
            ]
        );

        if (null === $model->getData('event_value')) {
            $model->setData('event_value', Event::DEFAULT_EVENT_X_VALUE);
        }
        $fieldset->addField(
            'event_value',
            'text',
            [
                'name' => 'event_value',
                'label' => __('X equals to'),
                'title' => __('X equals to'),
                'class' => "validate-number validate-greater-than-zero validate-digits",
                'required' => true
            ]
        );

        if (null === $model->getData('effect')) {
            $model->setData('effect', \Aheadworks\Popup\Model\Source\Effect::DEFAULT_VALUE);
        }
        /** @var \Aheadworks\Popup\Model\Source\Effect $effectSource */
        $effectSource = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Popup\Model\Source\Effect::class);
        $fieldset->addField(
            'effect',
            'select',
            [
                'name' => 'effect',
                'label' => __('Animation Effect'),
                'title' => __('Animation Effect'),
                'options' => $effectSource->getOptionArray()
            ]
        );

        if (null === $model->getData('cookie_lifetime')) {
            $model->setData('cookie_lifetime', Event::DEFAULT_COOKIE_LIFETIME_VALUE);
        }
        $fieldset->addField(
            'cookie_lifetime',
            'text',
            [
                'name' => 'cookie_lifetime',
                'label' => __('Cookie Lifetimes, minutes'),
                'title' => __('Cookie Lifetimes, minutes'),
                'class' => "validate-number validate-greater-than-zero validate-digits",
                'note' => __('Once popup is shown to the customer, it will not be shown to him again within the next'
                    . 'X minutes'),
                'required' => true
            ]
        );

        $fieldset = $form->addFieldset(
            'position_fieldset',
            [
                'legend' => __('Where to Display'),
            ]
        );

        if (null === $model->getData('page_type')) {
            $model->setData('page_type', PageType::DEFAULT_VALUE);
        }

        /** @var PageType $pageSource */
        $pageSource = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(PageType::class);
        $fieldset->addField(
            'page_type',
            'multiselect',
            [
                'name' => 'page_type',
                'label' => __('Display At'),
                'title' => __('Display At'),
                'values' => $pageSource->toOptionArray(),
                'required' => true
            ]
        );

        if (null === $model->getData('position')) {
            $model->setData('position', Position::DEFAULT_VALUE);
        }
        /** @var Position $positionSource */
        $positionSource = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(Position::class);
        $fieldset->addField(
            'position',
            'select',
            [
                'name' => 'position',
                'label' => __('Position'),
                'title' => __('Position'),
                'options' => $positionSource->getOptionArray()
            ]
        );

        /* PRODUCT SECTION */

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            [
                'legend' => __("Conditions (don't add conditions if rule is applied to all products)"),
            ]
        )->setRenderer(
            $this->rendererFieldsetFactory->create()
                ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
                ->setNewChildUrl(
                    $this->getUrl(
                        '*/*/newConditionHtml',
                        [
                            'form'   => $form->getHtmlIdPrefix().'conditions_fieldset',
                            'prefix' => 'popup',
                            'rule'   => base64_encode(\Aheadworks\Popup\Model\Rule\Product::class)
                        ]
                    )
                )
        );

        $model
            ->getRuleModel()
            ->getConditions()
            ->setJsFormObject($form->getHtmlIdPrefix() . 'conditions_fieldset');

        $fieldset
            ->addField(
                'popupconditions',
                'text',
                [
                    'name' => 'popupconditions',
                    'label' => __('Conditions'),
                    'title' => __('Conditions'),
                ]
            )
            ->setRule($model->getRuleModel())
            ->setRenderer($this->conditions)
        ;

        /* END PRODUCT SECTION */

        /* CATALOG SECTION */

        $block = $this->getLayout()->createBlock(
            \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree::class,
            null,
            ['data' => ['js_form_object' => "awPopupCategoryIds"]]
        )->setCategoryIds(
            explode(',', $model->getCategoryIds())
        );

        $fieldset = $form->addFieldset(
            'category_fieldset',
            [
                'legend' => ""
            ]
        );
        $categoryTitle = __('Categories');
        $fieldset
            ->addField(
                'category_ids',
                'hidden',
                [
                    'name' => 'category_ids',
                    'label' => __('Categories'),
                    'title' => __('Categories'),
                    'after_element_js' =>
                        "<script type='text/javascript'>
                            awPopupCategoryIds = {updateElement : {value : '', linkedValue : ''}};
                            Object.defineProperty(awPopupCategoryIds.updateElement, 'value', {
                                get: function() { return awPopupCategoryIds.updateElement.linkedValue},
                                set: function(v) {
                                    awPopupCategoryIds.updateElement.linkedValue = v; 
                                    jQuery('#popup_category_ids').val(v)
                                }
                            });
                        </script>"
                        ."<label class='label admin__field-label'><span>
                         {$categoryTitle}
                        </span>
                        </label>"
                        . $block->toHtml()

                ]
            );

        /* END CATALOG SECTION */

        /* add field dependences */
        $categoryType = PageType::CATEGORY_PAGE;
        $productType = PageType::PRODUCT_PAGE;

        $afterDurationEvent = Event::AFTER_DURATION;
        $pageScrolledEvent = Event::PAGE_SCROLLED;
        $viewedPagesEvent = Event::VIEWED_PAGES;

        $fieldset
            ->addField(
                'dependences',
                'note',
                [
                    'name' => 'dependences',
                    'label' => '',
                    'title' => '',
                    'after_element_html' => "
                        <script type='text/javascript'>
                            require(['jquery', 'awPopupManagerFieldset'], function($){
                                $.awPopupManagerFieldset.addDependence(
                                        '#{$form->getHtmlIdPrefix()}'+'category_fieldset', 
                                        '#{$form->getHtmlIdPrefix()}'+'page_type', 
                                        ['{$categoryType}']
                                        );
                                $.awPopupManagerFieldset.addDependence(
                                        '#{$form->getHtmlIdPrefix()}'+'conditions_fieldset', 
                                        '#{$form->getHtmlIdPrefix()}'+'page_type', 
                                        ['{$productType}']
                                        );

                                $.awPopupManagerFieldset.addDependence(
                                        '.field-event_value', 
                                        '#{$form->getHtmlIdPrefix()}'+'event', 
                                        ['{$afterDurationEvent}', '{$pageScrolledEvent}', '{$viewedPagesEvent}']
                                        );
                                $.awPopupManagerFieldset.addDependenceForClass(
                                        '#{$form->getHtmlIdPrefix()}'+'event_value', 
                                        'validate-digits-range', 
                                        '#{$form->getHtmlIdPrefix()}'+'event', 
                                        ['{$pageScrolledEvent}']
                                        );
                                $.awPopupManagerFieldset.addDependenceForClass(
                                        '#{$form->getHtmlIdPrefix()}'+'event_value', 
                                        'digits-range-1-100', 
                                        '#{$form->getHtmlIdPrefix()}'+'event', 
                                        ['{$pageScrolledEvent}']
                                        );
                                $.awPopupManagerFieldset.addDependenceForClass(
                                        '#{$form->getHtmlIdPrefix()}'+'event_value', 
                                        'required-entry', 
                                        '#{$form->getHtmlIdPrefix()}'+'event', 
                                        ['{$pageScrolledEvent}']
                                        );
                            });
                        </script>"
                ]
            );

        $fieldset = $form->addFieldset(
            'content_fieldset',
            [
                'legend' => __('Popup Content'),
            ]
        );

        if ($this->moduleManager->isMagePageBuilderModuleEnabled()) {
            $fieldset->addField(
                '_content',
                'hidden',
                [
                    'name' => '_content',
                    'after_element_html' => $this->pageBuilderRenderer->renderComponent($model->getContent())
                ]
            );
        } else {
            $fieldset->addField(
                'content',
                'editor',
                [
                    'name' => 'content',
                    'label' => __('Content'),
                    'title' => __('Content'),
                    'style' => 'height:36em',
                    'config' => $this->wysiwygConfig->getConfig()
                ]
            );
        }

        $fieldset = $form->addFieldset(
            'design_fieldset',
            [
                'legend' => __('Popup Design'),
            ]
        );

        $fieldset->addField(
            'custom_css',
            'textarea',
            [
                'name' => 'custom_css',
                'label' => __('Custom CSS'),
                'title' => __('Custom CSS'),
                'note' => __('Insert custom CSS to change popup design. If left blank, default design will be used.')
            ]
        );

        /**
         * Add preview link. It opens home page in new tab and shows popup with current info
         */
        $fieldset->addField(
            'preview',
            'note',
            [
                'text' => "<a href='#' class='action-default scalable' onclick='jQuery.awPreviewPopup.showPreview();'"
                    . "target='_blank'>".__('Preview')."</a>",
                'after_element_html' => "
                        <script type='text/javascript'>
                            require(
                            [\"jquery\"], function(jQuery){
                                jQuery.awPreviewPopup = {
                                    showPreview: function() {
                                        if (jQuery('[data-index=\"aw_pb_container\"]').length > 0) {
                                            content = jQuery('[name=\"content\"]').val();
                                        } else {
                                            var content = jQuery('#{$form->getHtmlIdPrefix()}'+'content').val();
                                            if (typeof (wysiwygpopup_content) !== 'undefined') {
                                                if (wysiwygpopup_content.decodeWidgets !== undefined) {
                                                    content = wysiwygpopup_content.decodeWidgets(content);
                                                } else if (wysiwygpopup_content.wysiwygInstance !== undefined
                                                && wysiwygpopup_content.wysiwygInstance.decodeContent !== undefined) {
                                                    content = 
                                                        wysiwygpopup_content.wysiwygInstance.decodeContent(content);
                                                }
                                            }
                                        }
                                        jQuery.ajax({
                                            url: '{$this->getUrl('*/*/preview')}',
                                            type: 'POST',
                                            dataType: 'json',
                                            context: this,
                                            async: false,
                                            data: {
                                                isAjax: 'true',
                                                popup_content: content,
                                                custom_css: jQuery('#{$form->getHtmlIdPrefix()}'+'custom_css').val(),
                                                effect: jQuery('#{$form->getHtmlIdPrefix()}'+'effect').val(),
                                                position: jQuery('#{$form->getHtmlIdPrefix()}'+'position').val()
                                            },
                                            showLoader: true,
                                            complete: function(response) {
                                                try {
                                                    eval('var json = ' + response.responseText + ' || {}');
                                                } catch (e) {
                                                    return false;
                                                }
                                                jQuery('#" .
                                                    $form->getHtmlIdPrefix() .
                                                    "'+'preview a').attr('href', json.preview_url
                                                    );
                                            }
                                        });

                                    }
                                };
                                return jQuery.awPreviewPopup;
                            });
                        </script>"
            ]
        );

        if (null !== $model->getId()) {
            $fieldset = $form->addFieldset(
                'statistic_fieldset',
                [
                    'legend' => __('Statistics'),
                ]
            );

            $fieldset->addField(
                'view_count',
                'label',
                [
                    'label' => __('Views'),
                ]
            );

            $fieldset->addField(
                'click_count',
                'label',
                [
                    'label' => __('Clicks'),
                ]
            );

            $fieldset->addField(
                'ctr',
                'label',
                [
                    'label' => __('CTR'),
                ]
            );

            $previewButton = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class,
                '',
                [
                    'data' => [
                        'type' => 'button',
                        'label' => __('Reset Statistics'),
                        'onclick' => "jQuery.awPopupStatistic.reset()",
                    ]
                ]
            );

            $fieldset->addField(
                'reset',
                'note',
                [
                    'text' => $previewButton->toHtml(),
                    'after_element_html' => "
                        <script type='text/javascript'>
                            require(
                            [\"jquery\"], function(jQuery){
                                jQuery.awPopupStatistic = {
                                    reset: function() {
                                        jQuery.ajax({
                                            url: '{$this->getUrl('*/*/resetStatistic')}',
                                            type: 'POST',
                                            dataType: 'json',
                                            context: this,
                                            showLoader: true,
                                            data: {
                                                isAjax: 'true',
                                                popup_id: {$model->getId()}
                                            },
                                            complete: function() {
                                                jQuery('.field-view_count .control-value').html(0);
                                                jQuery('.field-click_count .control-value').html(0);
                                                jQuery('.field-ctr .control-value').html('0%');
                                            }
                                        });
                                    }
                                };
                                return jQuery.awPopupStatistic;
                            });
                        </script>"
                ]
            );
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
