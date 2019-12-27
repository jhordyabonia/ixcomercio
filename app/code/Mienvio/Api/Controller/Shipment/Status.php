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
class Status extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface 
{

    const USER = 'shipping/mienvio_api/user';

	const PASSWORD = 'shipping/mienvio_api/password';

	const TOKEN = 'carriers/mienviocarrier/apikey';

	const ENVIROMENT = 'shipping/mienvio_api/apuntar_a';

	const URL_STAGING = 'shipping/mienvio_api/url_staging';

	const URL_PRODUCCION = 'shipping/mienvio_api/url_produccion';
    
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
    protected $_orderCollectionFactory;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * @var OrderViewAuthorizationInterface
     */
    protected $orderAuthorization;
    
    /**
     * 
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger_interface
     * @param \Magento\Framework\Controller\ResultFactory $result
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Trax\Catalogo\Helper\Email $email
     * @param \Trax\Ordenes\Model\IwsOrderFactory $iwsOrder,
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderAuthorization
     */
    public function __construct(
            Context $context, 
            PageFactory $resultPageFactory, 
            \Magento\Framework\App\Request\Http $request, 
            \Magento\Checkout\Model\Session $checkoutSession,
            \Psr\Log\LoggerInterface $logger_interface,
            \Magento\Framework\Controller\ResultFactory $result,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Trax\Catalogo\Helper\Email $email,
            \Trax\Ordenes\Model\IwsOrderFactory $iwsOrder,
            \Magento\Sales\Model\OrderFactory $orderFactory,
            \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderAuthorization 
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger_interface;        
        $this->resultRedirect = $result;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $email;
        $this->_iwsOrder = $iwsOrder;
        $this->orderFactory = $orderFactory;
        $this->orderAuthorization = $orderAuthorization;
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        if($customerSession->isLoggedIn()) {
            if($this->getRequest()->getParam('order_id') != null){
                $order_id = (int)$this->getRequest()->getParam('order_id', false);
                $order = $this->orderFactory->create()->load($order_id);
                if ($this->orderAuthorization->canView($order)) {
                    /*TODO:
                    Verificar estado de la orden en tabla iws_order
                    - Si no hay guia generada mostrar cierta información
                    - Si hay guía generada mostrar información de seguimiento
                    - Si ya se entrego el pedido mostrar la información del pedido entregado
                     */
                    $iws_order = $this->loadIwsData($order_id);
                    if($iws_order){
                        $resultPage = $this->resultPageFactory->create();
                        $resultPage->getConfig()->getTitle()->set((__('Order # '.$order->getRealOrderId())));
                        $resultPage->getLayout()->initMessages();          
                        try {               
                            $resultPage->getLayout()->getBlock('mienvio_status')->setTitle("Order Status");     
                            $resultPage->getLayout()->getBlock('mienvio_status')->setOrderStatus($order->getStatus());     
                            $resultPage->getLayout()->getBlock('mienvio_status')->setOrderGuide($iws_order->getMienvioGuide()); 
                            $resultPage->getLayout()->getBlock('mienvio_status')->setOrderDelivery($iws_order->getMienvioDelivery()); 
                            if($iws_order->getMienvioGuide() == 1){
                                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                                $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
                                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                                //Se obtienen parametros de configuración por Store
                                $configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode());
                                $mienvio_data = $this->loadMienvioData($configData, $order->getMienvioQuoteId());
                                $resultPage->getLayout()->getBlock('mienvio_status')->setMienvioData($mienvio_data);
                            }
                        } catch (\Exception $e) {
                            $this->logger->error('#SUCCESS', array('message' => $e->getMessage(), 'code' => $e->getCode(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()));
                            $resultPage->getLayout()->getBlock('mienvio_status')->setTitle("Error");
                        }        
                        return $resultPage;
                    } else {
                        $this->messageManager->addError( __('There was an error checking the order information.') );
                        $this->_redirect('sales/order/history');                 
                    }
                } else {
                    $this->messageManager->addError( __('There was an error checking the order information.') );
                    $this->_redirect('sales/order/history');                 
                }
            } else {    
                $this->messageManager->addError( __('There was an error checking the order information.') );
                $this->_redirect('sales/order/history');
            }
        } else {
            $this->messageManager->addError( __('To check the tracking information of your order you must login.') );
            $this->_redirect('customer/account/');
        }
    }

    //Obtiene los parámetros de configuración desde el cms
    public function getConfigParams($storeScope, $websiteCode) 
    {
        $configData['user'] = $this->scopeConfig->getValue(self::USER, $storeScope, $websiteCode);
        $configData['password'] = $this->scopeConfig->getValue(self::PASSWORD, $storeScope, $websiteCode);
        $configData['token'] = $this->scopeConfig->getValue(self::TOKEN, $storeScope, $websiteCode);
        $enviroment = $this->scopeConfig->getValue(self::ENVIROMENT, $storeScope, $websiteCode);
        //Se valida entorno para obtener url del servicio
        if($enviroment == '0'){
            $configData['url'] = $this->scopeConfig->getValue(self::URL_STAGING, $storeScope, $websiteCode);
        } else{
            $configData['url'] = $this->scopeConfig->getValue(self::URL_PRODUCCION, $storeScope, $websiteCode);
        }
        return $configData;

    }

    //Se guarda información de IWS en tabla custom
    public function loadIwsData($order_id) 
    {
        $orders = $this->_iwsOrder->create();
        $orders->getResource()
            ->load($orders, $order_id, 'order_id');
        if($orders->getId()){
            return $orders;
        }
        return false;
	}

    //Se añade comentario interno a orden
    public function addOrderComment($orderId, $comment) 
    {
		try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $order->addStatusHistoryComment($comment);
            $order->save();
        } catch (\Exception $e) {
            $this->logger->info('Mienviowebhook - Error al guardar comentario en orden con ID: '.$orderId);
        }
	}

    //Se consume el servicio de mi envio para quotes
    public function loadMienvioData($configData, $quote_id) 
    {     
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $configData['url'].$quote_id
        ));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$configData['token'])
        );
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($curl);
        curl_close($curl);    
        $this->logger->info('Mienvio API - quote_id: '.$quote_id);
        $this->logger->info('Mienvio API - status code: '.$status_code);
        $this->logger->info('Mienvio API - '.$configData['url']);
        $this->logger->info('Mienvio API - curl errors: '.$curl_errors);
        if ($status_code == '200'){
            $response = array(
                'status' => true,
                'resp' => json_decode($resp)
            );
        } else {
            $response = array(
                'status' => false,
                'status_code' => $status_code
            );
        }
        return $response;
	}
}
