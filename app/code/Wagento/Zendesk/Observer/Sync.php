<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Sync implements ObserverInterface
{
    /**
     * @var \Wagento\Zendesk\Helper\Api\User
     */
    private $user;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customerFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;
    /**
     * @var \Magento\Customer\Model\Logger
     */
    private $customerLogger;
    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    private $groupFactory;

    /**
     * Sync constructor.
     * @param \Wagento\Zendesk\Helper\Api\User $user
     * @param \Magento\Customer\Model\Customer|\Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\Logger $customerLogger
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @internal param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Wagento\Zendesk\Helper\Api\User $user,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Logger $customerLogger,
        \Magento\Customer\Model\GroupFactory $groupFactory
    ) {
    
        $this->user = $user;
        $this->customerFactory = $customerFactory;
        $this->scopeConfig = $scopeConfig;
        $this->orderFactory = $orderFactory;
        $this->customerLogger = $customerLogger;
        $this->groupFactory = $groupFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $observer->getData('customer'); // login
        if (!$customer) {
            $customer = $observer->getData('customer_data_object'); // register
        }

        if (!($customer instanceof \Magento\Customer\Api\Data\CustomerInterface)) {
            return;
        }

        $zdUserIdAttribute = $customer->getCustomAttribute('zd_user_id');

        if ($zdUserIdAttribute && $zdUserIdAttribute->getValue()) {
            // validate $zdUserIdAttribute
            $zdUserId = $zdUserIdAttribute->getValue();
            $data = $this->user->showUser($zdUserId);

            $updateCustomer = !isset($data['id']) || $data['id'] != $zdUserId;
            $zdUserId = $updateCustomer && isset($data['id']) ? $data['id'] : $zdUserId;
        } else {
            $email = $customer->getEmail();

            // look for already created user
            $user = $this->user->searchUsers($email);

            if ($user !== null && count($user) == 1 && isset($user[0]['id'])) {
                $zdUserId = $user[0]['id'];
            } else { // create user
                $data = [
                    'name' => $customer->getFirstname() . " " . $customer->getLastname(),
                    'email' => $customer->getEmail(),
                    'type' => 'end-user'
                ];
                $zdUserId = $this->user->createUser($data);
            }
            $updateCustomer = is_numeric($zdUserId);
        }

        if (is_numeric($zdUserId) && $this->scopeConfig->getValue(AdminZendesk::PATH_ZENDESK_CUSTOMER_FIELDS_ENABLE)) {
            $customFields = $this->getCustomerCustomFields($customer);
            $this->user->updateUser($zdUserId, ['user_fields' => $customFields]);
        }

        if ($updateCustomer) {
            $customer->setCustomAttribute('zd_user_id', $zdUserId);
            try {
                $customerInstance = $this->customerFactory->create();
                $customerInstance->updateData($customer);
                $customerInstance->save();
            } catch (\Exception $exception) {
                $exception->getMessage();
            }
        }
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return array
     */
    private function getCustomerCustomFields(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $configuredFields = $this->scopeConfig->getValue(AdminZendesk::PATH_ZENDESK_CUSTOMER_ATTRIBUTES_TO_SYNC);
        $configuredFieldsList = array_flip(explode(',', $configuredFields));

        $email = $customer->getEmail();
        $customerId = $customer->getId();
        $groupId = $customer->getGroupId();

        $attribute = [
            "id" => $customerId,
            "group" => $this->getGroup($groupId),
            "logged_in" => $this->getLastLoggedIn($customerId),
            "average_sale" => $this->getAverageSale($email),
            "lifetime_sale" => $this->getLifetimeSale($email)
        ];

        return array_intersect_key($attribute, $configuredFieldsList);
    }

    /**
     * @param $email
     * @return int|string
     */
    private function getLifetimeSale($email)
    {

        // Order Connection
        $order = $this->orderFactory->create();
        $orderResource = $order->getResource();
        $orderConnection = $orderResource->getConnection();
        $orderTable = $orderResource->getMainTable();

        $select = $orderConnection->select()->from(
            $orderTable,
            ['lifetime_sales' => 'SUM(subtotal_invoiced)']
        )->where('customer_email LIKE ?', $email);

        $select_res = $orderConnection->fetchOne($select);
        $lifetimeSales = isset($select_res) && is_numeric($select_res) ? $select_res : 0;
        return (float)$lifetimeSales;
    }

    /**
     * @param $email
     * @return float
     */
    private function getAverageSale($email)
    {
        // Order Connection
        $order = $this->orderFactory->create();
        $orderResource = $order->getResource();
        $orderConnection = $orderResource->getConnection();
        $orderTable = $orderResource->getMainTable();

        $select = $orderConnection->select()->from(
            $orderTable,
            ['average_sale' => 'AVG( IFNULL( subtotal_invoiced, 0 ) )']
        )->where('customer_email LIKE ?', $email);

        $select_res = $orderConnection->fetchOne($select);
        $averageSale = isset($select_res) && is_numeric($select_res) ? $select_res : 0;
        return (float)$averageSale;
    }

    /**
     * @param $customerId
     * @return string
     */
    private function getLastLoggedIn($customerId)
    {
        return $this->customerLogger->get($customerId)->getLastLoginAt();
    }

    /**
     * @param $groupId
     * @return string
     */
    private function getGroup($groupId)
    {
        /** @var \Magento\Customer\Model\Group $group */
        $group = $this->groupFactory->create();
        return $group->load($groupId)->getCode();
    }
}
