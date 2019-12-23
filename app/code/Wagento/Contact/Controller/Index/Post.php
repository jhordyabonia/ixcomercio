<?php
/**
 *
 * Copyright Â© Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Contact\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Wagento\Zendesk\Helper\Api\Ticket;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;

class Post extends \Magento\Contact\Controller\Index implements HttpPostActionInterface
{
    const PATH_ZENDESK_CONTACT_US_IX_ENABLE = 'zendesk/ticket/frontend/contact_us_ix';
    const PATH_ZENDESK_CONTACT_US_IX_TAGS   = 'zendesk/ticket/frontend/contact_us_tags';
    
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var MailInterface
     */
    private $mail;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     * @param MailInterface $mail
     * @param DataPersistorInterface $dataPersistor
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ConfigInterface $contactsConfig,
        MailInterface $mail,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger = null,
        \Wagento\Zendesk\Helper\Api\Ticket $ticket,
        \Wagento\Zendesk\Helper\Api\User $userApi,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Wagento\Zendesk\Helper\Data $helper,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context, $contactsConfig);
        $this->context = $context;
        $this->mail = $mail;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->ticket = $ticket;
        $this->userApi = $userApi;
        $this->customerFactory = $customerFactory;
        $this->helper = $helper;
        $this->authSession = $authSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;

    }

    /**
     * Post user question
     *
     * @return Redirect
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        try {
            $post  = $this->validatedParams();
            $store = $this->storeManager->getStore();

            $scope     = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;            
            $storeCode = $store->getCode();
            $enableCreateTicket = $this->scopeConfig->getValue(self::PATH_ZENDESK_CONTACT_US_IX_ENABLE, $scope, $storeCode);
          
            $post['scope']              = $scope;
            $post['storeCode']          = $storeCode;
            $post['enableCreateTicket'] = $enableCreateTicket;
           
            if ($enableCreateTicket){
                $this->createTicket($post);
            }
            $this->sendEmail($post);
            $this->messageManager->addSuccessMessage(
                    __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->dataPersistor->clear('contact_us');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        }
        return $this->resultRedirectFactory->create()->setPath('contact/index');
    }

    /**
     * @param array $post Post data from contact form
     * @return void
     */
    private function sendEmail($post)
    {
        $this->mail->send(
            $post['email'],
            ['data' => new DataObject($post)]
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function validatedParams()
    {
        $request = $this->getRequest();
        if (trim($request->getParam('name')) === '') {
            throw new LocalizedException(__('Enter the Name and try again.'));
        }
        if (trim($request->getParam('comment')) === '') {
            throw new LocalizedException(__('Enter the comment and try again.'));
        }
        if (false === \strpos($request->getParam('email'), '@')) {
            throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
        }
        if (trim($request->getParam('hideit')) !== '') {
            throw new \Exception();
        }

        return $request->getParams();
    }

    public function createTicket($data)
    {
        
        $requester     = trim($data["email"]);
        $requesterName = trim($data["name"]);

        $store         = $this->storeManager->getStore();

        $websiteId     = $store->getWebsiteId();

        /** Create the Request Id */
        $requestId = $this->createRequest($requester, $requesterName, $websiteId);

        $tags       = $this->scopeConfig->getValue(self::PATH_ZENDESK_CONTACT_US_IX_TAGS,$data['scope'], $data['storeCode']);
        $tagsList   = explode(',',$tags);
        $tagsList[] = $store->getName();
        $ticket = [
                'requester_id' => $requestId,
                'submitter_id' => $requestId,
                'subject' => (isset($data['subject']) && !empty($data['subject']))?$data['subject']: 'Nuevo Ticket' ,
                'comment' => [
                    'body' => $data['comment']
                ],
	            'tags' => $tagsList
        ];
        
        // Add extra attributes validation
        $status = $this->scopeConfig->getValue('zendesk/ticket/status',$data['scope'], $data['storeCode']);
        if ($status) {
            $ticket['status'] = $status;
        }
        else
            $ticket['status'] = 'new';
        $type = $this->scopeConfig->getValue('zendesk/ticket/type',$data['scope'], $data['storeCode']);
        if ($type) {
            $ticket['type'] = $type;
        }
        else
            $ticket['type'] = 'incident';
        $priority = $this->scopeConfig->getValue('zendesk/ticket/priority',$data['scope'], $data['storeCode']);
        if ($priority) {
            $ticket['priority'] = $priority;
        }
        else
            $ticket['priority'] = 'normal';
  
        $ticketId = $this->ticket->create($ticket);
        if (isset($ticketId)) {
            $this->messageManager->addSuccessMessage(sprintf('Ticket creado correctamente, el # es %s.', $ticketId));
            return true;
        }

        $this->messageManager->addErrorMessage("Error al crear un nuevo ticket.");
        return false;
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
}
