<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Model\System\Message;

class ConfigureAccount implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var \Wagento\Zendesk\Helper\Api\Connector
     */
    private $connector;

    /**
     * ConfigureAccount constructor.
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Wagento\Zendesk\Helper\Api\Connector $connector
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Wagento\Zendesk\Helper\Api\Connector $connector
    ) {
    
        $this->urlBuilder = $urlBuilder;
        $this->connector = $connector;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return 'zendesk_rest_connection';
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        // Remove zd direct request and do a one time check async
        return !$this->connector->validateConfiguredConnection();
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        $url = $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/zendesk');
        return __('<a href="%1">Click here</a> to configure Zendesk connection.', $url);
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }
}
