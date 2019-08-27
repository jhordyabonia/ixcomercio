<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Block\System\Config\Form;

class Customersync extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var string
     */
    protected $_template = 'Wagento_Zendesk::system/config/button/sync.phtml';

    /**
     * @var \Magento\MediaStorage\Model\File\Storage
     */
    protected $_fileStorage;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\MediaStorage\Model\File\Storage $fileStorage
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\MediaStorage\Model\File\Storage $fileStorage,
        array $data = []
    ) {
    
        $this->_fileStorage = $fileStorage;
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate synchronize button html
     *
     * @return string
     */
    public function getButtonHtml()
    {

        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'zendesk_customer_sync_customer_btn',
                'label' => __('Synchronize Customer'),
            ]
        );
        return $button->toHtml();
    }
    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('zendesk/customer_ajax/sync');
    }

    /**
     * @return array
     */
    public function getScopeConfig()
    {
        $params = $this->getRequest()->getParams();
        $config = [
            'scope' => \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            'scopeId' => 0,
        ];
        if (isset($params['website'])) {
            $config['scope'] = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
            $config['scopeId'] = $params['website'];
        }
        if (isset($params['store'])) {
            $config['scope'] = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
            $config['scopeId'] = $params['store'];
        }
        return $config;
    }
}
