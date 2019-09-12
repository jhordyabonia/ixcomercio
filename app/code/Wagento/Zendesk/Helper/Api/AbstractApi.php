<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Helper\Api;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * This improves defaults magento's http implementation for curl
 * Class AbstractApi
 * @package Wagento\Zendesk\Helper\Api
 */
abstract class AbstractApi extends AbstractHelper
{
    /**
     * @var \Wagento\Zendesk\Helper\Data
     */
    protected $zendeskHelper;
    /**
     * @var Sources\Client
     */
    private $client;

    /**
     * AbstractApi constructor.
     * @param Context $context
     * @param Sources\Client $client
     * @param \Wagento\Zendesk\Helper\Data $zendeskHelper
     */
    public function __construct(
        Context $context,
        \Wagento\Zendesk\Helper\Api\Sources\Client $client,
        \Wagento\Zendesk\Helper\Data $zendeskHelper
    ) {
    
        parent::__construct($context);
        $this->zendeskHelper = $zendeskHelper;
        $this->client = $client;
    }

    /**
     * @param string $endpoint
     * @param array
     */
    protected function get($endpoint, $params = [])
    {
        $this->prepareAuth();
        if (count($params) > 0) {
            $args = [];
            foreach ($params as $arg => $val) {
                $args[] = urlencode($arg) . '=' . urlencode($val);
            }
            $endpoint .= '?' . implode('&', $args);
        }
        $uri = $this->zendeskHelper->buildUri($endpoint);
        $this->client->send('GET', $uri);
        return $this->client->getBody();
    }

    /**
     * @param string $endpoint
     * @param array $params
     */
    protected function post($endpoint, $params = [])
    {
        $this->prepareAuth();
        $uri = $this->zendeskHelper->buildUri($endpoint);
        $this->client->send('POST', $uri, $params);
        return $this->client->getBody();
    }

    /**
     * @param string $endpoint
     * @param array $params
     */
    protected function oauthPost($endpoint, $params = [])
    {
        $uri = $this->zendeskHelper->buildUri($endpoint);
        $this->client->send('POST', $uri, $params);
        return $this->client->getBody();
    }

    /**
     * @param string $endpoint
     * @param $params
     */
    protected function put($endpoint, $params)
    {
        $this->prepareAuth();
        $uri = $this->zendeskHelper->buildUri($endpoint);
        $this->client->send('PUT', $uri, $params);
        return $this->client->getBody();
    }

    /**
     * @param string $endpoint
     */
    protected function delete($endpoint)
    {
        $this->prepareAuth();
        $uri = $this->zendeskHelper->buildUri($endpoint);
        $this->client->send('DELETE', $uri);
    }

    /**
     * Get Url
     * @param $object
     * @param $id
     *
     * @return string
     */
    public function getUrl($object, $id)
    {
        return $this->zendeskHelper->getUrl($object, $id);
    }

    /**
     * Prepare Header request once api is available
     */
    private function prepareAuth()
    {
        $this->client->setHeaders(
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->zendeskHelper->getToken()
            ]
        );
    }
}
