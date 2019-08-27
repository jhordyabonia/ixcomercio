<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Block\Ticket;

use Magento\Framework\View\Element\Template;

class View extends Template
{
    const PATH_CAN_COMMENT = 'zendesk/ticket/frontend/customer_ticket_view_comment';
    /**
     * @var \Wagento\Zendesk\Helper\Api\Comment
     */
    private $comment;
    /**
     * @var \Wagento\Zendesk\Helper\Api\User
     */
    private $user;
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
     * @param Template\Context $context
     * @param \Wagento\Zendesk\Helper\Api\Comment $comment
     * @param \Wagento\Zendesk\Helper\Api\User $user
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Wagento\Zendesk\Helper\Api\Comment $comment,
        \Wagento\Zendesk\Helper\Api\User $user,
        \Magento\Customer\Model\Session $customerSession,
        \Wagento\Zendesk\Helper\Api\Ticket $ticket,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->comment = $comment;
        $this->user = $user;
        $this->customerSession = $customerSession;
        $this->ticket = $ticket;
    }

    /**
     * Get Ticket Comments.
     *
     * @return array
     */
    public function getComments()
    {
        $ticketId = $this->getTicketId();
        return $this->comment->getTicketComments($ticketId);
    }

    /**
     * @return array|bool
     */
    public function getTicket()
    {
        $ticketId = $this->getTicketId();
        return $this->ticket->showTicket($ticketId);
    }

    /**
     * Get user names.
     *
     * @param $userId
     * @return mixed
     */
    public function getUserName($userId)
    {
        $data = $this->user->showUser($userId);
        return $data['name'];
    }

    /**
     * Get Action Url.
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_urlBuilder->getUrl('*/ticket/commentpost');
    }

    /**
     * Get Ticket Id.
     *
     * @return mixed
     */
    public function getTicketId()
    {
        return $this->getRequest()->getParam('id', null);
    }

    /**
     * Get Author Id
     *
     * @return bool|mixed
     */
    public function getAuthorId()
    {
        $customer = $this->customerSession->getCustomerDataObject();
        $zdUserIdAttr = $customer->getCustomAttribute('zd_user_id');

        // load ticket by Zendesk User ID
        if ($zdUserIdAttr) {
            return $zdUserIdAttr->getValue();
        }
        return false;
    }

    /**
     * @return bool
     */
    public function canComment()
    {
        return $this->_scopeConfig->getValue(self::PATH_CAN_COMMENT);
    }
}
