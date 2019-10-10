<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Block;

use Magento\Framework\View\Element\Template;

class Tickets extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Wagento\Zendesk\Helper\Api\User
     */
    protected $userApi;
    /**
     * @var \Wagento\Zendesk\Helper\Api\Ticket
     */
    private $ticket;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Tickets constructor.
     * @param Template\Context $context
     * @param \Wagento\Zendesk\Helper\Api\Ticket $ticket
     * @param \Wagento\Zendesk\Helper\Api\User $userApi
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Wagento\Zendesk\Helper\Api\Ticket $ticket,
        \Wagento\Zendesk\Helper\Api\User $userApi,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->ticket = $ticket;
        $this->userApi = $userApi;
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return array
     */
    public function getTickets()
    {
        $customer = $this->customerSession->getCustomerDataObject();
        $zdUserIdAttr = $customer->getCustomAttribute('zd_user_id');

        // load ticket by Zendesk User ID
        if ($zdUserIdAttr) {
            $zdUserId = $zdUserIdAttr->getValue();
            return array_reverse($this->ticket->listUserTickets($zdUserId));
        }

        return [];
    }

    public function getOrderTickets()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if ($orderId) {
            /** @var \Magento\Sales\Api\Data\OrderInterface $order */
            $order = $this->orderRepository->get($orderId);
            $isGuest = $order->getCustomerIsGuest();
            $customerId = $order->getCustomerId();
            $zdUserId = null;

            if (!$isGuest && isset($customerId)) {
                $customer = $this->customerSession->getCustomerDataObject();
                // load customer from session
                if ($customer->getId() == $customerId) {
                    $zdUserIdAttr = $customer->getCustomAttribute('zd_user_id');

                    // load ticket by Zendesk User ID
                    if ($zdUserIdAttr) {
                        $zdUserId = $zdUserIdAttr->getValue();
                    }
                }
            }

            // NEXT: discuss posibility to list guest order ticket
            $tickets = $this->ticket->listUserTickets($zdUserId);

            $orderNumber = $order->getIncrementId();
            $zdOrderFieldId = $this->_scopeConfig->getValue('zendesk/ticket/order_field_id');

            foreach ($tickets as $k => $ticket) {
                $fieldList = array_column($ticket['custom_fields'], 'value', 'id');
                if (isset($fieldList[$zdOrderFieldId]) && $fieldList[$zdOrderFieldId] == $orderNumber) {
                    continue;
                }
                unset($tickets[$k]);
            }
            return $tickets;
        }

        return [];
    }

    /**
     * @return array
     */
    public function getRecentTickets()
    {

        $tickets = $this->getTickets();
        return array_slice($tickets, 0, 5);
    }

    /**
     * @param $ticketId
     * @return string
     */
    public function getViewUrl($ticketId)
    {
        return $this->_urlBuilder->getUrl('zendesk/ticket/view', ['id' => $ticketId]);
    }

    /**
     * @return string
     */
    public function getViewAllUrl()
    {
        return $this->getUrl('zendesk/customer/tickets');
    }
}
