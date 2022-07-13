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

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Aheadworks\Popup\Model\ThirdPartyModule\PageBuilder\PageBuilderFactory;

/**
 * Class PageBuilderRenderer
 * @package Aheadworks\Popup\Block\Adminhtml
 */
class PageBuilderRenderer extends Template
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Popup::page_builder_container.phtml';

    /**
     * @var PageBuilderFactory
     */
    private $pageBuilderFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var string
     */
    private $content;

    /**
     * @param Context $context
     * @param PageBuilderFactory $pageBuilderFactory
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        PageBuilderFactory $pageBuilderFactory,
        Json $json,
        $data = []
    ) {
        parent::__construct($context, $data);
        $this->json = $json;
        $this->pageBuilderFactory = $pageBuilderFactory;
    }

    /**
     * Get Page Builder UI component config
     *
     * @return false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPageBuilderConfig()
    {
        $components = [];
        $pageBuilder = $this->pageBuilderFactory->create(
            [
                'data' => [
                    'formElement' => 'wysiwyg',
                    'wysiwyg' => true,
                    'name' => 'aw_pagebuilder'
                ]
            ]
        );

        $config = $pageBuilder->getConfiguration();
        $config['exports'] = [
            'value' => 'pb_container.aw_pb_container:value'
        ];
        if ($this->content != '') {
            $config['value'] = $this->content;
        }

        $pageBuilder->setData('config', $config);

        $components['page_builder'] = $pageBuilder->getData();
        $components['aw_pb_container'] = [
            'formElement' => 'textarea',
            'component' => 'Magento_Ui/js/form/element/textarea',
            'template' => 'ui/form/field',
            'dataScope' => 'pb_container.content',
            'dataType' => 'textarea',
            'config' => [
                'cols' => 1,
                'rows' => 1,
                'visible' => false
            ]
        ];

        $config = [
            'component' => 'uiComponent',
            'name' => 'aw_pagebuilder_container',
            'children' => $components
        ];

        return $this->json->serialize($config);
    }

    /**
     * Render UI Component
     *
     * @param string $value
     * @return string
     */
    public function renderComponent($value = '')
    {
        $this->content = $value;

        return $this->toHtml();
    }
}
