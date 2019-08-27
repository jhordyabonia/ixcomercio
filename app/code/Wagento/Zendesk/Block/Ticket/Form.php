<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Block\Ticket;

use Magento\Framework\View\Element\Template;

class Form extends Template
{
    /**
     * @var \Wagento\Zendesk\Model\Config\Source\Ticket\Status
     */
    private $status;
    /**
     * @var \Wagento\Zendesk\Model\Config\Source\Ticket\Priority
     */
    private $priority;
    /**
     * @var \Wagento\Zendesk\Model\Config\Source\Ticket\Priority
     */
    private $type;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSession;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;
    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    /**
     * Form constructor.
     * @param Template\Context $context
     * @param \Wagento\Zendesk\Model\Config\Source\Ticket\Status $status
     * @param \Wagento\Zendesk\Model\Config\Source\Ticket\Priority $priority
     * @param \Wagento\Zendesk\Model\Config\Source\Ticket\Type $type
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Wagento\Zendesk\Model\Config\Source\Ticket\Status $status,
        \Wagento\Zendesk\Model\Config\Source\Ticket\Priority $priority,
        \Wagento\Zendesk\Model\Config\Source\Ticket\Type $type,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->status = $status;
        $this->priority = $priority;
        $this->type = $type;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerSession = $customerSession;
        $this->orderConfig = $orderConfig;
    }

    /**
     * Get Customer Orders.
     *
     * @return array
     */
    public function getOrders()
    {
        if (!($customerId = $this->customerSession->create()->getCustomerId())) {
            return [];
        }
        $this->orders = $this->orderCollectionFactory->create($customerId)
            ->addFieldToSelect(
                ['value' => 'entity_id', 'label' => 'increment_id']
            )->addFieldToFilter(
                'status',
                ['in' => $this->orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );
        return $this->orders->getData();
    }

    /**
     * Get Action Url for post data.
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_urlBuilder->getUrl('*/ticket/createPost');
    }

    /**
     * Get Requested Order Id.
     *
     * @return mixed
     */
    public function getRequestedOrderId()
    {
        return $this->getRequest()->getParam('orderid', false);
    }
}
