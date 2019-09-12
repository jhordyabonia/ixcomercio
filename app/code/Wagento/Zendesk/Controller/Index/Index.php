<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ResponseInterface;
use Wagento\Zendesk\Controller\AbstractOauth;
use Wagento\Zendesk\Helper\Api\Connector;

class Index extends AbstractOauth
{

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * Ticket constructor.
     * @param Context $context
     * @param Connector $connector
     * @internal param Data $urlHelper
     */
    public function __construct(
        Context $context,
        Connector $connector
    ) {
    
        parent::__construct($context);
        $this->connector = $connector;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        if ($this->validateRequest($this->getRequest(), $params)) {
            if (isset($params['code'])) {
                if ($this->connector->generateToken($params['code'], $token)) {
                    $this->connector->saveToken($token);
                }
            } else {
                if ($this->connector->validateConfig($params)) {
                    $uri = $this->connector->getAuthUrl();
                    return $resultRedirect->setPath($uri);
                }
            };
            return $this->_windowClose();
        }
        return $resultRedirect->setPath('/');
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param $params
     * @return bool
     */
    protected function validateRequest(\Magento\Framework\App\RequestInterface $request, &$params)
    {
        $params = $request->getParams();

        // check if is hash
        if (count($params) == 1) {
            $encodedData = array_keys($params)[0];
            if ($this->validateHash($encodedData)) {
                $params = $encodedData;
                return true;
            }
        }
        return isset($params['code']) || isset($params['error']);
    }

    /**
     * @param string $hash
     * @return bool
     */
    protected function validateHash(&$hash)
    {
        $hash = json_decode(base64_decode($hash), true);
        return is_array($hash) && isset($hash['client_id']) && isset($hash['subdomain']) && isset($hash['client_secret']);
    }
}
