<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Controller\Adminhtml\Connector\Ajax;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Revoke extends Action
{
    /**
     * @var \Wagento\Zendesk\Helper\Data
     */
    private $zendeskHelper;

    /**
     * Revoke constructor.
     * @param Action\Context $context
     * @param \Wagento\Zendesk\Helper\Data $zendeskHelper
     */
    public function __construct(
        Action\Context $context,
        \Wagento\Zendesk\Helper\Data $zendeskHelper
    ) {
    
        parent::__construct($context);
        $this->zendeskHelper = $zendeskHelper;
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
        $this->zendeskHelper->revokeToken();
        return $resultJson->setData(['success' => 'success']);
    }
}
