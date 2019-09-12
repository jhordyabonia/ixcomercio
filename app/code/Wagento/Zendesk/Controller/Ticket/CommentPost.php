<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Controller\Ticket;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class CommentPost extends \Wagento\Zendesk\Controller\AbstractUserAuthentication
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Wagento\Zendesk\Helper\Api\Ticket
     */
    private $ticket;

    /**
     * CommentPost constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Wagento\Zendesk\Helper\Api\Ticket $ticket
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Wagento\Zendesk\Helper\Api\Ticket $ticket
    ) {
    
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->ticket = $ticket;
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
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $customer = $this->customerSession->getCustomerDataObject();
        $zdUserIdAttr = $customer->getCustomAttribute('zd_user_id');

        if ($zdUserIdAttr) {
            $commentData = [
                "author_id" => $zdUserIdAttr->getValue(),
                "body" => $this->getRequest()->getParam('comment'),
            ];

            $ticketId = $this->getRequest()->getParam('code');
            $res = $this->ticket->update($ticketId, ['comment' => $commentData]);

            if ($res) {
                $this->messageManager->addSuccessMessage(__('Your comment was succesfully send.'));
                return $resultRedirect->setPath('*/*/view', ['id' => $res]);
            }
        }
    }
}
