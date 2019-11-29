<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Create extends Action
{
    /**
     * @var \Wagento\Zendesk\Helper\Api\Ticket
     */
    private $ticket;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Wagento\Zendesk\Helper\Api\User
     */
    protected $userApi;

    /**
     * @var \Wagento\Zendesk\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

     /**
     * Create constructor.
     * @param Action\Context $context
     * @param \Wagento\Zendesk\Helper\Api\Ticket $ticket
     */
    public function __construct(
        Action\Context $context,
        \Wagento\Zendesk\Helper\Api\Ticket $ticket,
        \Wagento\Zendesk\Helper\Api\User $userApi,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Wagento\Zendesk\Helper\Data $helper,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    
        parent::__construct($context);
        $this->ticket = $ticket;
        $this->userApi = $userApi;
        $this->customerFactory = $customerFactory;
        $this->helper = $helper;
        $this->authSession = $authSession;
        $this->storeManager = $storeManager;
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

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage('Can\' create ticket please try again');
            return $resultRedirect->setPath('*/*/edit');
        }

        $data = $this->getRequest()->getParams();

        $requester = trim($data["requester"]);
        if (!empty($data["customer_email"])) {
            $requester = $data["customer_email"];
        }
        $requesterName = trim($data["requester_name"]);
        $websiteId = trim($data["website_id"]);
        $storeName = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getName();
        /** Create the Request Id */
        $requestId = $this->createRequest($requester, $requesterName, $websiteId);

        $ticket = [
                'requester_id' => $requestId,
                'submitter_id' => $requestId,
                'subject' => $data['subject'],
                'status' => $data['status'],
                'priority' => $data['priority'],
                'comment' => [
                    'body' => $data['description']
                ],
	            'tags' => [$storeName]	
        ];
        /** Add additional options  */
        if (isset($data['type']) && strlen(trim($data['type'])) > 0) {
            $ticket['type'] = $data['type'];
        }

        $fieldId = $this->helper->getOrderField();
        if ($fieldId && isset($data['order_id']) && strlen(trim($data['order_id'])) > 0) {
            $ticket['custom_fields'][] = [
                'id' => $fieldId,
                'value' => $data['order_id']
            ];
        }

        $ticketId = $this->ticket->create($ticket);
        if (isset($ticketId)) {
            $this->messageManager->addSuccessMessage(sprintf('Ticket Created successfully, the id is %s.', $ticketId));
            return $resultRedirect->setPath('*/*');
        }

        $this->messageManager->addErrorMessage("Error while creating new ticket, Make sure that connection was configured correctly.");
        return $resultRedirect->setPath('*/*/edit');
    }

    /**
     * Load the Customer
     * @param string $requester
     * @param string $requesterName
     * @param int $websiteId
     * @return int $requestId
     */
    public function createRequest($requester, $requesterName, $websiteId)
    {
        $requesterId = null;
        $user = null;
        $customer = $this->customerFactory->create();
        /**  Customer email address can be used in multiple websites so
         *   we need to explicitly scope it */
        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $customer->setWebsiteId($websiteId)->loadByEmail($requester);
        } else {
            $customer->loadByEmail($requester);
        }

        if ($customer && $customer->getId()) {
            //$requesterId = $customer->getZendeskRequesterId();
            // If the requester name hasn't already been set, then set it to the customer name
            if (strlen($requesterName) == 0) {
                $requesterName = $customer->getName();
            }
        }
        $user = $this->userApi->getUser($requester);

        if (isset($user["id"])) {
            $requesterId = $user["id"];
        } else {
            $requesterId = $this->getRequestIdNewUser($requester, $requesterName);
        }

        return $requesterId;
    }

    /**
     * Create User and retrieve the Request Id
     * @param string $requester
     * @param string $requesterName
     * @return  \Magento\Framework\Controller\Result\Redirect | int $requesterId
     */
    public function getRequestIdNewUser($requester, $requesterName)
    {
        $requesterId = null;
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $data = [
                'name' => $requesterName,
                'email' => $requester,
                'type' => 'end-user'
            ];
            $user = $this->userApi->createUser($data);
            if (is_array($user) && isset($user["id"])) {
                $requesterId = $user['id'];
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*');
        }
        return $requesterId;
    }

    /**
     * Return the submitter Id
     * @return int $submiterId
     * @deprecated
     */
    public function getSubmitterId()
    {
        $submitterId = null;
        $adminUser = $this->authSession->getUser();
        $user = $this->userApi->getUser($adminUser->getEmail());

        if (isset($user["id"])) {
            $submitterId = $user["id"];
        } elseif ($meUser = $this->userApi->getMeUser()) {
            if ($meUser && is_array($meUser) && isset($meUser["id"])) {
                $submitterId = $meUser["id"];
            }
        }
        return $submitterId;
    }
}
