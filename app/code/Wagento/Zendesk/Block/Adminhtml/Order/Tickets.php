<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;

class Tickets extends Template
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Wagento\Zendesk\Helper\Api\Ticket
     */
    private $ticket;

    /**
     * Tickets constructor.
     * @param Template\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Wagento\Zendesk\Helper\Api\Ticket $ticket
     * @param \Magento\Store\Api\Data\StoreConfigInterface $storeConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Wagento\Zendesk\Helper\Api\Ticket $ticket,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->ticket = $ticket;
    }

    /**
     * Return Order Created Tickets.
     *
     * @return array
     */
    public function getTickets()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if ($orderId) {
            /** @var \Magento\Sales\Api\Data\OrderInterface $order */
            $order = $this->orderRepository->get($orderId);
            $isGuest = $order->getCustomerIsGuest();
            $customerId = $order->getCustomerId();
            $zdUserId = null;

            if (!$isGuest && isset($customerId)) {
                $customer = $this->customerRepository->getById($customerId);
                $zdUserIdAttr = $customer->getCustomAttribute('zd_user_id');

                // load ticket by Zendesk User ID
                if ($zdUserIdAttr) {
                    $zdUserId = $zdUserIdAttr->getValue();
                }
            }

            //NEXT discuss posibility to list guest order ticket
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
}
