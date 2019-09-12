<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Controller\Adminhtml\Connector\Ajax;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Validation extends Action
{
    /**
     * @var \Wagento\Zendesk\Helper\Api\Connector
     */
    private $connector;

    /**
     * Validation constructor.
     * @param Action\Context $context
     * @param \Wagento\Zendesk\Helper\Api\Connector $connector
     */
    public function __construct(
        Action\Context $context,
        \Wagento\Zendesk\Helper\Api\Connector $connector
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
        // ajax validation
        if (!$this->getRequest()->isAjax()) {
            return $this->_forward('noroute');
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $requestParams = $this->getRequest()->getParams();
        $configuredValues = $this->connector->getConfiguredValues();

        if (isset($requestParams['client_secret'])
            && preg_match('/^\*+$/', $requestParams['client_secret']) // verify if is *** to raplace with a valid saved value
            && isset($configuredValues['client_secret'])
        ) {
            // no spaces and empty validation
            if (preg_match('/^$|\s+/', $configuredValues['client_secret'])) {
                return $resultJson->setData(['error' => 'Secret is invalid.']);
            }
            $requestParams['client_secret'] = $configuredValues['client_secret'];
        }

        $hash = base64_encode(json_encode($requestParams));

        return $resultJson->setData(['install' => 'install', 'hash' => $hash]);
    }
}
