<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Block\System\Config\Form;

class Oauth extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var string
     */
    protected $_template = 'Wagento_Zendesk::system/config/button/oauth.phtml';
    /**
     * @var \Wagento\Zendesk\Helper\Data
     */
    private $zendeskHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Wagento\Zendesk\Helper\Data $zendeskHelper
     * @param array $data
     * @internal param \Magento\Framework\App\Helper\AbstractHelper $helper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Wagento\Zendesk\Helper\Data $zendeskHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->zendeskHelper = $zendeskHelper;
        $this->scopeConfig = $scopeConfig;
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
     * @return string
     */
    public function getOauthAjaxUrl()
    {
        return $this->getUrl('zendesk/connector_ajax/validation');
    }

    /**
     * @return string
     */
    public function getRevokeAjaxUrl()
    {
        return $this->getUrl('zendesk/connector_ajax/revoke');
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
     * Generate oauth button html
     *
     * @return string
     */
    public function getOauthButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'oauth',
                'label' => __('Authorize'),
            ]
        );
        return $button->toHtml();
    }

    public function getRevokeOauthButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'revoke_oauth',
                'label' => __('Revoke Authorization'),
            ]
        );
        return $button->toHtml();
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

    /**
     * @return string
     */
    public function hasToken()
    {
        $token = $this->zendeskHelper->getToken();
        return $token ? true : false;
    }

    /**
     * @return mixed
     */
    public function getFrontBaseUrl() {
        // REPEATED refactor needed
        return $this->scopeConfig->getValue('web/secure/base_url');
    }
}
