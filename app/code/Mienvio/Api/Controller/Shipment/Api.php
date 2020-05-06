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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/mienvio_api.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer); 
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

    private function getBodyWebhook(){
        $validCalls = ['shipment.status', 'shipment.upload'];
        //Se obtiene el body
        $json = file_get_contents('php://input');
        //$json = '{"type":"shipment.status","body":{"quote_id":28486},"version":""}';
        $this->logger->info($json);
        $body = @json_decode($json, false);
        //Verifica el body
        if($body === null && json_last_error() !== JSON_ERROR_NONE){
            throw new \Exception('no fue posible leer el json de entrada: ' . json_last_error());
        }
        //Verifica que se trate de una entrada válida
        if(!isset($body->type) || !in_array($body->type, $validCalls)){
            throw new \Exception('Tipo de entrada no válida');
        }
        $this->logger->info($body->type);
        return $body;
    }

    /**
     * Load the page defined in view/frontend/layout/bancomer_index_webhook.xml
     * URL /openpay/payment/success
     * 
     * @url https://magento.stackexchange.com/questions/197310/magento-2-redirect-to-final-checkout-page-checkout-success-failed?rq=1
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
        $this->logger->info('INICIA PROCESO DE API');
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->jsonResultFactory->create();
        try{
            //Se obtienen las cabeceras
            $headers = array();
            foreach (getallheaders() as $name => $value) {
                $headers[$name] = $value;
            }
            //Se obtiene el body
            $body = $this->getBodyWebhook();
            //Obtiene la configuración
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode());
            $this->logger->info(print_r($configData, true));
            $this->updateMienvioData($body->type, $body->body->quote_id);
            $result->setHttpResponseCode(200);
            $result->setData(['success_message' => __('Updated data')]);
        }catch(\Exception $e){
            $this->logger->info("Error: {$e->getMessage()}");
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
            $result->setData(['error_message' => __('Error:' . $e->getMessage())]);
        }
        $this->logger->info('FINALIZA PROCESO DE API');
        return $result;
        
        
        
        //Se verifica si hay una cabecera asociada al token
        if(isset($headers['token'])){
            
            if(hash('sha256', $configData['user'].','.$configData['password']) == $headers['token']){
                $body = json_decode(file_get_contents('php://input'));
                
            } else{
                $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
                $result->setData(['error_message' => __('Unauthorized')]);
            }
        } else {
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
            $result->setData(['error_message' => __('Unauthorized')]);
        }
        
    }

    //Obtiene los parámetros de configuración desde el cms
    public function getConfigParams($storeScope, $websiteCode) 
    {
        $configData['user'] = $this->scopeConfig->getValue(self::USER, $storeScope, $websiteCode);
        $configData['password'] = $this->scopeConfig->getValue(self::PASSWORD, $storeScope, $websiteCode);
        return $configData;

    }

    //Se verifica body de la petición
    //DELETE
    public function checkBody($body) 
    {
        $result = $this->updateMienvioData($body->type, $body->body->quote_id);
        return $result;
    }

    //Agrega notificación de guía
    public function updateMienvioData($type, $quote_id) 
    {
        $order_id = $this->loadOrderInformation($quote_id);
        $notification = $this->saveMienvioData($type, $order_id);
    }

    //Se carga la orden relacionada a la cotización
    public function loadOrderInformation($quote_id) 
    {
        $this->logger->info("Mienvio quote_id: {$quote_id}");
        $collection = $this->_orderCollectionFactory->create()->addFieldToSelect('*')->addFieldToFilter('mienvio_quote_id', $quote_id);
        if(!$collection->getSize()){
            throw new \Exception('Error al obtener información de la orden con mienvio_quote_id: '.$quote_id);
        }
        $order = $collection->getFirstItem();
        $this->logger->info("Magento order_id: {$order->getEntityId()}");
        return $order->getEntityId();
	}

    //Se guarda información de IWS en tabla custom
    public function saveMienvioData($type, $order_id) 
    {
        $orders = $this->_iwsOrder->create();
        $orders->getResource()->load($orders, $order_id, 'order_id');
        if($orders->getId()){
            $update = 0;
            try{
                switch($type){
                    case 'shipment.upload':
                        if($orders->getMienvioGuide()==0){
                            $orders->setMienvioGuide(1);
                            $update = 1;
                            $this->addOrderComment($order_id, "Se ha generado la guía de la orden");
                        }
                        break;
                    case 'shipment.status':
                        if($orders->getMienvioDelivery()==0){
                            $orders->setMienvioDelivery(1);
                            $update = 1;
                            $this->addOrderComment($order_id, "El pedido ha sido entregado");
                        }
                        break;
                }
                if($update == 1){
                    $orders->save();
                    $this->logger->info('Mienviowebhook - Se actualizo la orden : '.$orders->getId());
                } else {
                    $this->logger->info('Mienviowebhook - La orden con id : '.$orders->getId().' ya se encontraba actualizada');
                }
                return true;
            } catch (\Exception $e) {
                $this->logger->info('Mienviowebhook - Error al actualizar la orden con id: '.$orders->getId());
            }
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
}
