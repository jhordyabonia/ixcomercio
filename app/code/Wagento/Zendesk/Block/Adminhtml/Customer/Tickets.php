<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Block\Adminhtml\Customer;

use Magento\Backend\Block\Template;

class Tickets extends Template
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    private $customerRepository;
    /**
     * @var \Wagento\Zendesk\Helper\Api\Ticket
     */
    private $ticket;

    /**
     * Tickets constructor.
     * @param Template\Context $context
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Wagento\Zendesk\Helper\Api\Ticket $ticket
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Wagento\Zendesk\Helper\Api\Ticket $ticket,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->customerRepository = $customerRepository;
        $this->ticket = $ticket;
    }

    /**
     * Return ticket list.
     *
     * @return array
     */
    public function getTickets()
    {
        $customerId = $this->getRequest()->getParam('id');

        if ($customerId) {
            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            $customer = $this->customerRepository->getById($customerId);
            $zdUserIdAttr = $customer->getCustomAttribute('zd_user_id');

            // load ticket by Zendesk User ID
            if ($zdUserIdAttr) {
                $zdUserId = $zdUserIdAttr->getValue();
                return array_reverse($this->ticket->listUserTickets($zdUserId));
            }
        }
        return [];
    }
}
