<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Controller\Ticket;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Wagento\Zendesk\Controller\AbstractUserAuthentication;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CreatePost extends AbstractUserAuthentication
{
    /**
     * @var \Wagento\Zendesk\Helper\Api\Ticket
     */
    private $ticket;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * CreatePost constructor.
     * @param Context $context
     * @param \Wagento\Zendesk\Helper\Api\Ticket $ticket
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        \Wagento\Zendesk\Helper\Api\Ticket $ticket,
        \Magento\Customer\Model\Session $customerSession,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    
        parent::__construct($context);
        $this->ticket = $ticket;
        $this->customerSession = $customerSession;
    $this->scopeConfig = $scopeConfig;
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

        $customer = $this->customerSession->getCustomerDataObject();
        $customerAttribute = $customer->getCustomAttribute('zd_user_id');

        $backParams = [];
        if ($customerAttribute) {
            $endUserId = $customerAttribute->getValue();
            $data  = $this->getRequest()->getParams();
            $store = $this->storeManager->getStore();

            $params = [
                'requester_id' => $endUserId,
                'submitter_id' => $endUserId,
                'subject' => (isset($data['subject']) && !empty($data['subject']))?$data['subject']: 'Nuevo Ticket' ,
                'comment' => [
                    'body' => $data['comment']
	        ],
            'tags' => [$store->getName()]
            ];

            $scope     = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;            
            $storeCode = $store->getCode();
           
            // Add extra attributes validation
            $status = $this->scopeConfig->getValue('zendesk/ticket/status', $scope, $storeCode);
            if ($status) {
                $params['status'] = $status;
            }
            else
                $params['status'] = 'new';
            $type = $this->scopeConfig->getValue('zendesk/ticket/type', $scope, $storeCode);
            if ($type) {
                $params['type'] = $type;
            }
            else
                $params['type'] = 'incident';
            $priority = $this->scopeConfig->getValue('zendesk/ticket/priority', $scope, $storeCode);
            if ($priority) {
                $params['priority'] = $priority;
            }
            else
                $params['priority'] = 'normal';
            // Verify order number send
            if (isset($data['order']) && $data['order'] != -1) {
                $ticketFieldId = $this->scopeConfig->getValue('zendesk/ticket/order_field_id');
                $params['custom_fields'][] = [
                    'id' => $ticketFieldId,
                    'value' => $data['order']
                ];
                // assign order id param in case something went wrong
                $backParams['orderid'] = $data['order'];
            }   
            $response = $this->ticket->create($params);
            if (is_numeric($response)) {
                $this->messageManager->addSuccessMessage('Ticket create successfully.');
                return $resultRedirect->setPath('*/customer/tickets');
            }
        }

        $this->messageManager->addErrorMessage('Try again, if problem persist contact with store support.');
        return $resultRedirect->setPath('*/*/create', $backParams);
    }
}
