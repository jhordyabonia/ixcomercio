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
    protected $_orderCollectionFactory;
    
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
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
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
            \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory 
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
        $this->_orderCollectionFactory = $orderCollectionFactory;
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
                $body = json_decode(file_get_contents('php://input'));
                $result = $this->checkBody($body);
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
        $configData['user'] = $this->scopeConfig->getValue(self::USER, $storeScope, $websiteCode);
        $configData['password'] = $this->scopeConfig->getValue(self::PASSWORD, $storeScope, $websiteCode);
        return $configData;

    }

    //Se verifica body de la petición
    public function checkBody($body) 
    {
        $result = $this->updateMienvioData($body->type, $body->body->quote_id);
        $result->setHttpResponseCode(200);
        $result->setData(['success_message' => __('Authorized')]);
        return $result;
    }

    //Agrega notificación de guía
    public function updateMienvioData($type, $quote_id) 
    {        
        $order_id = $this->loadOrderInformation($quote_id);
        if($order_id){
            $notification = $this->saveMienvioData($type, $order_id);
            if($notification){
                $result->setHttpResponseCode(200);
                $result->setData(['success_message' => __('Authorized')]);
            } else{
                $result->setHttpResponseCode(204);
                $result->setData(['success_message' => __('No Content')]);
            }
        } else {
            $result->setHttpResponseCode(204);
            $result->setData(['success_message' => __('No Content')]);
        }
        return $result;
    }

    //Se carga la orden relacionada a la cotización
    public function loadOrderInformation($quote_id) 
    {
		try {
            $collection = $this->_orderCollectionFactory->create()->addFieldToSelect('*')->addFieldToFilter('mienvio_quote_id', $quote_id);
            if($collection->getSize()){
                $data = $collection->getFirstItem();
                return $data->getEntityId();
            }
        } catch (\Exception $e) {
            $this->logger->info('Mienviowebhook - Error al obtener información de la orden con mienvio_quote_id: '.$quote_id);
        }
        return false;
	}

    //Se guarda información de IWS en tabla custom
    public function saveMienvioData($type, $order_id) 
    {
		$model = $this->_iwsOrder->create();
        switch($type){
            case 'shipment.upload':
                $model->addData([
                    "order_id" => $order_id,
                    "mienvio_guide" => 1,
                    ]);
                break;
            case 'shipment.status':
                $model->addData([
                    "order_id" => $order_id,
                    "mienvio_delivery" => 1,
                    ]);
                break;
        }
        $saveData = $model->save();
        if($saveData){
            $this->logger->info('Mienviowebhook - Se actualizo la orden : '.$order_id);
            return true;
        } else {
            $this->logger->info('Mienviowebhook - Se produjo un error al actualizar la orden: '.$order_id);
        }
        return false;
	}
}
