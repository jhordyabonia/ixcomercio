<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Controller\Ticket;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class View extends \Wagento\Zendesk\Controller\AbstractUserAuthentication
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
     * View constructor.
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
        $customer = $this->customerSession->getCustomerDataObject();
        $zdUserIdAttr = $customer->getCustomAttribute('zd_user_id');

        if ($zdUserIdAttr) {
            $zdUserId = $zdUserIdAttr->getValue();
            $ticketId = $this->getRequest()->getParam('id');
            $ticket = $this->ticket->showTicket($ticketId);

            // NEXT: In  next release verify fields submitter_id and requester_id this maybe different in some cases
            if ($ticket && $zdUserId == $ticket['submitter_id'] && $zdUserId == $ticket['requester_id']) {
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
                $resultPage->getConfig()->getTitle()->set(__('Ticket # %1 Conversation', $ticketId));
                return $resultPage;
            }
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $this->messageManager->addErrorMessage(__('Can\'t load ticket. Please choose one from list.'));
        return $resultRedirect->setPath('*/customer/tickets');
    }
}
