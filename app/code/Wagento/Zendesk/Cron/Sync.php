<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Cron;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class Sync
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $event;
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $context;
    /**
     * @var CollectionFactory
     */
    private $customerFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        CollectionFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->event = $context->getEventManager();
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $isEnabled = $this->scopeConfig->getValue('zendesk/customer/sync_by_cron');

        if($isEnabled) return;

        try {
            $customerCollection = $this->customerFactory->create();
            $customers = $customerCollection
                ->addAttributeToFilter(array(
                    array('attribute' => 'zd_user_id', 'null' => true),
                    array('attribute' => 'zd_user_id', 'eq' => ''),
                    array('attribute' => 'zd_user_id', 'eq' => 'NO FIELD')
                ),
                    '',
                    'left')
                ->setPageSize(100);

            /** @var \Magento\Customer\Model\Customer $customer */
            foreach ($customers as $customer) {
                /** @var \Magento\Customer\Api\Data\CustomerInterface $objectCustomer */
                $objectCustomer = $customer->getDataModel();
                $this->event->dispatch('customer_admin_sync', ['customer' => $objectCustomer]);
            }
        } catch (\Exception $exception) {
        }
    }
}
