<?php
/** 
 * @category    Mienvio
 * @package     Mienvio_Api
 * @author      Valentina Aguirre
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
 */

namespace Trax\Taxid\Controller\Invoice;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Sales\Model;
use Trax\Taxid\Helper\Data;
use Cdi\Custom\Helper\Api as CdiApi;

/**
 * Webhook class  
 */
class Api extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

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
    protected $_taxidHelper;
    protected $_cdiHelper;
    
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
            \Trax\Ordenes\Model\IwsOrderFactory $iwsOrder,
            \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
            \Trax\Taxid\Helper\Data $taxidHelper,
            \Cdi\Custom\Helper\Api $cdiHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/tax_id_api.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->_invoiceService = $invoiceService;
        $this->transactionBuilder = $transactionBuilder;
        $this->resultRedirect = $result;
        $this->_iwsOrder = $iwsOrder;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_taxidHelper = $taxidHelper;	
        $this->_cdiHelper = $cdiHelper;	
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
        $validCalls = array_keys($this->_taxidHelper->getValidCalls());
        //Se obtiene el body
        $json = file_get_contents('php://input');
        //PENDIENTE
        //$json = '{"type":"shipment.upload","body":{"quote_id":29556},"version":""}';
        $json = '{"type":"shipment.upload","triggerTime":{"date":"2020-05-14 21:02:14.359139","timezone_type":3,"timezone":"America\/Mexico_City"},"body":{"quote_id":29556},"version":"2020.05.14"}';
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
    public function execute(){
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
            //Actualiza la información
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
    }

    //Agrega notificación de guía
    public function updateMienvioData($type, $quote_id) 
    {
        $order = $this->loadOrderInformation($quote_id);
        $notification = $this->saveMienvioData($type, $order);
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
        return $order;
    }

    //Consume el WS
    //Se consume el servicio de mi envio para quotes
    public function loadMienvioData($configData, $quote_id){
        $header = $this->_taxidHelper->getOutcommingHeader($configData);
        $wsdl = $configData['url'].$quote_id;
        return $this->_cdiHelper->makeCurl($wsdl, $header, $this->logger);
    }
    
    //Obtiene la información del WS
    private function getWSData($type, $order){
        //Parámetros de configuración del WS
        $configKeys = $this->_taxidHelper->getValidCalls($type);
        $configData = $this->_cdiHelper->getConfigParams($configKeys['ws_config']);
        if(!$configData['enviroment']){
            $configData['url'] = $configData['url_stagging'];
        }else{
            $configData['url'] = $configData['url_prod'];
        }
        unset($configData['url_stagging'], $configData['url_prod']);
        //Consume el WS
        $mienvio_data_array = array();
        $mienvio_data = $this->loadMienvioData($configData, $order->getMienvioQuoteId());
        if(isset($mienvio_data['resp']->purchase) && count($mienvio_data['resp']->purchase->shipments)>0){
            $shipment = reset($mienvio_data['resp']->purchase->shipments);
            $mienvio_data_array['object_purpose'] = $shipment->object_purpose;
            $mienvio_data_array['status'] = $shipment->status;
            if(isset($shipment->label)){
                $label = (array) $shipment->label;
                $mienvio_data_array = array_merge($mienvio_data_array, $label);
            }
        }
        $this->logger->info('Información recibida del WS');
        $this->logger->info(print_r($mienvio_data_array, true));
        if(empty($mienvio_data_array)) throw new \Exception("No se pudo obtener información del WS de mi envío");
        return $mienvio_data_array;
    }

    //Se guarda información de IWS en tabla custom
    public function saveMienvioData($type, $order) 
    {
        $orders = $this->_iwsOrder->create();
        $orders->getResource()->load($orders, $order->getEntityId(), 'order_id');
        if($orders->getId()){
            $update = 0;
            try{
                switch($type){
                    case 'shipment.upload':
                    case 'shipment.status':                
                        $data = $this->getWSData($type, $order);
                        if($orders->getMienvioGuide() == 0){
                            $orders->setMienvioGuide(1);
                            $saved = array('status' => '');
                        }else{
                            $saved = unserialize($orders->getMienvioUploadResp());
                        }
                        //Obtiene el estado
                        if($saved['status'] != $data['status'] || true){
                            //Si el estado es diferente lo guarda y envía mensaje
                            $comment = $this->_taxidHelper->getCommentByStatus($data);
                            $this->_cdiHelper->addOrderComment(
                                $order, 
                                $comment['msg'],
                                $comment['notify'],
                                $comment['newstatus']
                            );
                            $orders->setMienvioUploadResp(serialize($data));
                            $orders->save();
                            $this->logger->info('Mienviowebhook - Se actualizo la orden : '.$orders->getId());
                        }else{
                            $this->logger->info('Mienviowebhook - La orden con id : '.$orders->getId().' ya se encontraba actualizada');
                        }
                        break;
                }
                return true;
            } catch (\Exception $e) {
                $this->logger->info('Mienviowebhook - Error al actualizar la orden con id: '.$orders->getId());
                throw $e;
            }
        }else{
            throw new \Exception("La orden {$order->getEntityId()} no cuenta con id IWS.");
        }
        return false;
    }
}
