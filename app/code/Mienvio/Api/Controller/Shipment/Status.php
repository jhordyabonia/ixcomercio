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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        if($customerSession->isLoggedIn()) {
            if(isset($this->getRequest()->getParam('order_id', false))){
                $order_id = $this->getRequest()->getParam('order_id', false);
                /*TODO:
                Validar que la orden corresponda al usuario con sesión
                - Si no corresponde redireccionar al dashboard con mensaje de advertencia
                 */
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set((__('Order # '.$order_id)));
                $resultPage->getLayout()->initMessages();          
                try {               
                    $resultPage->getLayout()->getBlock('mienvio_status')->setTitle("Entra aquí");     
                } catch (\Exception $e) {
                    $this->logger->error('#SUCCESS', array('message' => $e->getMessage(), 'code' => $e->getCode(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()));
                    $resultPage->getLayout()->getBlock('mienvio_status')->setTitle("Error");
                }        
                return $resultPage;
            } else {                
                /*TODO:
                Mensaje de advertencia de error en url
                 */
                $this->_redirect('customer/account/');
            }
        } else {
            $this->_redirect('customer/account/');
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
    public function checkBody($body) 
    {
        $result = $this->updateMienvioData($body->type, $body->body->quote_id);
        return $result;
    }

    //Agrega notificación de guía
    public function updateMienvioData($type, $quote_id) 
    {        
        $order_id = $this->loadOrderInformation($quote_id);
        $result = $this->jsonResultFactory->create();
        if($order_id){
            $notification = $this->saveMienvioData($type, $order_id);
            if($notification){
                $result->setHttpResponseCode(200);
                $result->setData(['success_message' => __('Updated data')]);
            } else{
                $result->setHttpResponseCode(204);
                $result->setData(['success_message' => __('Information not found')]);
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
        $orders = $this->_iwsOrder->create();
        $orders->getResource()
            ->load($orders, $order_id, 'order_id');
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
