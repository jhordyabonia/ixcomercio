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
use Magento\Sales\Model;
use Mienvio\Api\Helper\Data;
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
    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $jsonResultFactory;
    protected $_mienvioHelper;
    protected $_cdiHelper;
    protected $_simulate = array('json' => false, 'validate' => false);
    private $_dump;
    private $_externalLog;
    
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
            \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
            \Mienvio\Api\Helper\Data $mienvioHelper,
            \Cdi\Custom\Helper\Api $cdiHelper
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
        $this->jsonResultFactory = $jsonResultFactory;
        $this->_mienvioHelper = $mienvioHelper;	
        $this->_cdiHelper = $cdiHelper;
        $this->_dump = false;
        $this->_externalLog = false;
    }

    /**
     * Genera dump para depuración web
     */
    private function dump($obj, $die = false, $title = null){
        $this->_cdiHelper->dump($obj, $die, $title);
    }

    /**
     * Retorna el objeto logger, verifica si es el local
     * o si debe retornar uno externo.
     */
    private function getLoggerObj(){
        if($this->_externalLog){
            $logger = $this->_externalLog;
        }else{
            $logger = $this->logger;
        }
        return $logger;
    }

    /**
     * Agrega registros al log
     */
    private function log($str){
        $logger = $this->getLoggerObj();
        $str = ($this->_externalLog) ? "MIENVIO API: {$str}" : $str;
        $logger->info($str);
        if($this->_dump) echo "{$str}<br/>";
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
        $validCalls = array_keys($this->_mienvioHelper->getValidCalls());
        //Se obtiene el body
        $json = file_get_contents('php://input');
        //PENDIENTE
        if($this->_simulate['json']){
            $json = '{"type":"shipment.upload","triggerTime":{"date":"2020-05-14 21:02:14.359139","timezone_type":3,"timezone":"America\/Mexico_City"},"body":{"quote_id":29563},"version":"2020.05.14"}';
        }
        $this->log($json);
        $body = @json_decode($json, false);
        //Verifica el body
        if($body === null && json_last_error() !== JSON_ERROR_NONE){
            throw new \Exception('no fue posible leer el json de entrada: ' . json_last_error());
        }
        //Verifica que se trate de una entrada válida
        if(!isset($body->type) || !in_array($body->type, $validCalls)){
            throw new \Exception('Tipo de entrada no válida');
        }
        $this->log($body->type);
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
        $this->log('INICIA PROCESO DE API');
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
            $this->log("Error: {$e->getMessage()}");
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
            $result->setData(['error_message' => __('Error:' . $e->getMessage())]);
        }
        $this->log('FINALIZA PROCESO DE API');
        return $result;        
    }

    //Agrega notificación de guía
    public function updateMienvioData($type, $quote_id) 
    {
        list($order, $iwsOrder) = $this->loadOrderInformation($quote_id);
        $notification = $this->saveMienvioData($type, $order, $iwsOrder);
    }

    //Se carga la orden relacionada a la cotización
    public function loadOrderInformation($quote_id) 
    {
        $order = $this->_cdiHelper->getMagentoOrderBy(array(array('mienvio_quote_id', $quote_id)), $this->getLoggerObj());
        $iwsOrder = $this->_cdiHelper->getIwsOrderBy('order_id', $order->getEntityId(), $this->getLoggerObj());
        return array($order, $iwsOrder);
    }

    //Consume el WS
    //Se consume el servicio de mi envio para quotes
    public function loadMienvioData($configData, $quote_id){     
        $header = $this->_mienvioHelper->getOutcommingHeader($configData);
        $wsdl = $configData['url'].$quote_id;
        return $this->_cdiHelper->makeCurl($wsdl, $header, $this->getLoggerObj());   
	}
    
    //Obtiene la información del WS
    private function getWSData($type, $order){
        //Parámetros de configuración del WS
        $configKeys = $this->_mienvioHelper->getValidCalls($type);
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
        $this->log('Información recibida del WS');
        $this->log(print_r($mienvio_data_array, true));
        if(empty($mienvio_data_array)) throw new \Exception("No se pudo obtener información del WS de mi envío");
        return $mienvio_data_array;
    }

    //Se guarda información de IWS en tabla custom
    public function saveMienvioData($type, $order, $iwsOrder, $externalLog = false) 
    {
        //Si se consume la función desde un proceso externo, registra el log en ese proceso
        $this->_externalLog = $externalLog;
        try{
            switch($type){
                case 'shipment.upload':
                case 'shipment.status':                
                    $data = $this->getWSData($type, $order);
                    if($iwsOrder->getMienvioGuide() == 0){
                        $iwsOrder->setMienvioGuide(1);
                        $saved = array('status' => '');
                    }else{
                        $saved = unserialize($iwsOrder->getMienvioUploadResp());
                    }
                    //Obtiene el estado
                    if($saved['status'] != $data['status'] || $this->_simulate['validate']){
                        //Si el estado es diferente lo guarda y envía mensaje
                        $comment = $this->_cdiHelper->getCommentByStatus($data, 'shipment');
                        $this->_cdiHelper->addOrderComment(
                            $order, 
                            $comment['msg'],
                            $comment['notify'],
                            $comment['newstatus']
                        );
                        $iwsOrder->setMienvioUploadResp(serialize($data));
                        $iwsOrder->save();
                        if($externalLog){
                            return [true, $comment['newstatus']];
                        }else{
                            $this->log("Se actualizo la orden : {$iwsOrder->getId()}, nuevo estado: {$comment['newstatus']}");
                        }
                    }else{
                        if($externalLog){
                            return [false, false];
                        }else{
                            $this->log('La orden con id : '.$iwsOrder->getId().' ya se encontraba actualizada');
                        }
                    }
                    break;
            }
            return true;
        } catch (\Exception $e) {
            if(!$externalLog) $this->log('Error al actualizar la orden con id: '.$iwsOrder->getId());
            throw $e;
        }
        return false;
    }
}
