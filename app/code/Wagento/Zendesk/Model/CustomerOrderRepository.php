<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Model;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\OrderFactory;
use Wagento\Zendesk\Api\CustomerOrderRepositoryInterface;
use Wagento\Zendesk\Api\Data\CustomerOrderInterface;

/**
 * Class CustomerOrderRepository
 * @package Wagento\Zendesk\Model
 */
class CustomerOrderRepository implements CustomerOrderRepositoryInterface
{
    /**
     * @var CustomerOrderInterface
     */
    protected $customerOrder;
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var OrderFactory
     */
    private $orderFactory;
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * CustomerOrderRepository constructor.
     * @param CustomerOrderInterface $customerOrder
     * @param CustomerFactory $customerFactory
     * @param OrderFactory $orderFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        CustomerOrderInterface $customerOrder,
        CustomerFactory $customerFactory,
        OrderFactory $orderFactory,
        GroupRepositoryInterface $groupRepository,
        DataObjectHelper $dataObjectHelper
    ) {
    
        $this->customerFactory = $customerFactory;
        $this->customerOrder = $customerOrder;
        $this->orderFactory = $orderFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Loads a specified customer order information.
     *
     * @param string $email
     * @return \Wagento\Zendesk\Api\Data\CustomerOrderInterface Customer Order Interface.
     */
    public function get($email)
    {
        if (!$email) {
            throw new InputException(__('Email required'));
        }
        $customerInfo = $this->getCustomerData($email);

        if (!$customerInfo) {
            throw new NoSuchEntityException(__('Requested customer doesn\'t exist'));
        }

        $this->dataObjectHelper->populateWithArray(
            $this->customerOrder,
            $customerInfo,
            \Wagento\Zendesk\Api\Data\CustomerOrderInterface::class
        );

        return $this->customerOrder;
    }

    /**
     * @param string $email
     * @return array | false
     */
    private function getCustomerData($email)
    {
        /* Get customer info from table customer_entity */
        $customer = $this->customerFactory->create();
        $customerResource = $customer->getResource();
        $customerConnection = $customerResource->getConnection();
        $customerTable = $customerResource->getEntityTable();

        $select = $customerConnection->select()->from(
            $customerTable,
            [
                'email',
                'firstname',
                'lastname',
                'created_at',
                'group_id'
            ]
        )->where(
            'email LIKE ?',
            $email
        );
        $customerData = $customerConnection->fetchRow($select);

        // Order Connection
        $order = $this->orderFactory->create();
        $orderResource = $order->getResource();
        $orderConnection = $orderResource->getConnection();
        $orderTable = $orderResource->getMainTable();

        /** Maybe customer is Guest try to load from order  */
        if (!$customerData) {
            $select = $orderConnection->select()->from(
                $orderTable,
                [
                    'email' => 'customer_email',
                    'firstname' => 'customer_firstname',
                    'lastname' => 'customer_lastname',
                    'group_id' => 'customer_group_id'
                ]
            )->where(
                'customer_email = ?',
                $email
            )->order(['entity_id DESC'])
                ->limit(1);
            $customerData = $orderConnection->fetchRow($select);
        }

        // get group name
        try {
            if (isset($customerData['group_id']) && $customerData['group_id'] !== null) {
                $customerData['group'] = $this->groupRepository->getById($customerData['group_id'])->getCode();
            }
        } catch (NoSuchEntityException $e) {
            $customerData['group'] = null;
        }

        // lifetime sales
        $select = $orderConnection->select()->from(
            $orderTable,
            ['lifetime_sales' => 'SUM(subtotal_invoiced)']
        )->where('customer_email LIKE ?', $email);

        $select_res = $orderConnection->fetchOne($select);
        $lifetimeSales = isset($select_res) && is_numeric($select_res) ? $select_res : 0;

        $customerData['lifetime_sales'] = $this->customerOrder->formatPrice($lifetimeSales);

        // format created_at
        if (isset($customerData['created_at']) && $customerData['created_at']) {
            $customerData['created_at'] = $this->customerOrder->formatDate($customerData['created_at'], \IntlDateFormatter::MEDIUM);
        } else {
            $customerData['created_at'] = '-';
        }

        return $customerData;
    }
}
