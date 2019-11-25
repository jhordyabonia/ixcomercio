<?php
/** 
 * @category    Mienvio
 * @package     Mienvio_Api
 * @author      Valentina Aguirre
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
 */

namespace Mienvio\Api\Controller\Shipment;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

/**
 * Webhook class  
 */
class Api extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    const USER = 'shipping/mienvio_api/user';

	const PASSWORD = 'shipping/mienvio_api/password';
    
    private $helper;
	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $resultPageFactory;
    protected $request;
    protected $payment;
    protected $checkoutSession;
    protected $orderRepository;
    protected $logger;
    protected $_invoiceService;
    protected $transactionBuilder;
    protected $_iwsOrder;
    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $jsonResultFactory;
    
    /**
     * 
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger_interface
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Framework\Controller\ResultFactory $result
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Trax\Catalogo\Helper\Email $email
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     */
    public function __construct(
            Context $context, 
            PageFactory $resultPageFactory, 
            \Magento\Framework\App\Request\Http $request, 
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Psr\Log\LoggerInterface $logger_interface,
            \Magento\Sales\Model\Service\InvoiceService $invoiceService,
            \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
            \Magento\Framework\Controller\ResultFactory $result,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Trax\Catalogo\Helper\Email $email,
            \Trax\Ordenes\Model\IwsOrderFactory $iwsOrder,
            \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger_interface;        
        $this->_invoiceService = $invoiceService;
        $this->transactionBuilder = $transactionBuilder;
        $this->resultRedirect = $result;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $email;
        $this->_iwsOrder = $iwsOrder;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Load the page defined in view/frontend/layout/bancomer_index_webhook.xml
     * URL /openpay/payment/success
     * 
     * @url https://magento.stackexchange.com/questions/197310/magento-2-redirect-to-final-checkout-page-checkout-success-failed?rq=1
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {   
        //Se obtienen las cabeceras
        $headers = array();
        foreach (getallheaders() as $name => $value) {
            $headers[$name] = $value;
        } 
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->jsonResultFactory->create();
        //Se verifica si hay una cabecera asociada al token
        if(isset($headers['hash'])){
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            //Se obtienen parametros de configuración por Store
            $configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode());
            if(hash('sha256', $configData['user'].','.$configData['password']) == $headers['hash']){
                $result->setHttpResponseCode(200);
                $result->setData(['success_message' => __('Authorized')]);
            } else{
                $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
                $result->setData(['error_message' => __('Unauthorized')]);
            }
        } else {
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
            $result->setData(['error_message' => __('Unauthorized')]);
        }
        return $result;
    }

    //Obtiene los parámetros de configuración desde el cms
    public function getConfigParams($storeScope, $websiteCode) 
    {
        $enviroment = $this->scopeConfig->getValue(self::SANDBOX, $storeScope, $websiteCode);
        $configData['user'] = $this->scopeConfig->getValue(self::USER, $storeScope, $websiteCode);
        $configData['password'] = $this->scopeConfig->getValue(self::PASSWORD, $storeScope, $websiteCode);
        return $configData;

    }
}
