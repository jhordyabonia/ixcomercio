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
    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $jsonResultFactory;
    protected $_taxidHelper;
    protected $_cdiHelper;
    protected $_simulate = array('json' => false, 'validate' => false);
    
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
        $this->jsonResultFactory = $jsonResultFactory;
        $this->_taxidHelper = $taxidHelper;	
        $this->_cdiHelper = $cdiHelper;	
    }

    private function dump($obj, $die = true, $title = null){
        $this->_cdiHelper->dump($obj, $die, $title);
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
        if($this->_simulate['json']){
            $json = '{"type": "invoice.upload", "body": {"orderId": "196"}}';
        }
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
            $this->updateTraxData($body->type, $body->body->orderid);
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
    public function updateTraxData($type, $orderId)
    {
        list($order, $iwsOrder) = $this->loadOrderInformation($orderId);
        $notification = $this->saveInvoiceData($type, $order, $iwsOrder);
    }

    //Se carga la orden relacionada a la cotización
    public function loadOrderInformation($orderId)
    {
        $iwsOrder = $this->_cdiHelper->getIwsOrderBy('iws_order', $orderId, $this->logger); //order_increment_id|iws_order
        $order = $this->_cdiHelper->getMagentoOrderBy(array(array('entity_id', $iwsOrder->getOrderId())), $this->logger);
        return array($order, $iwsOrder);
    }

    //Consume el WS
    //Se consume el servicio de mi envio para quotes
    public function loadTraxData($configData, $iwsOrderId){
        $params = array('orderNumber' => $iwsOrderId);
        $wsdl = $this->_cdiHelper->prepateTraxUrl('getinvoice', $configData, $params, $this->logger);
        $try = 3; $sleep = 3;
        return $this->_cdiHelper->makeCurl($wsdl, false, $this->logger, $try, $sleep);
    }
    
    //Obtiene la información del WS
    private function getWSData($type, $iwsOrder){
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
        $trax_data_array = array();
        $trax_data = $this->loadTraxData($configData, $iwsOrder->getIwsOrder());
        if(isset($trax_data['status']) && $trax_data['status']){
            $obj = $trax_data['resp'];
            $trax_data_array['status'] = 'INVOICE_CREATED';
            $trax_data_array['InvoiceNumber'] = $obj->InvoiceNumber;
            $trax_data_array['InvoiceDate'] = $obj->InvoiceDate;
            $trax_data_array['TaxRegistrationNumber'] = $obj->TaxRegistrationNumber;
            $trax_data_array['InvoiceUrl'] = $obj->InvoiceUrl;
        }
        $this->logger->info('Información recibida del WS');
        $this->logger->info(print_r($trax_data_array, true));
        if(empty($trax_data_array)) throw new \Exception("No se pudo obtener información del WS de factura");
        return $trax_data_array;
    }

    //Se guarda información de IWS en tabla custom
    public function saveInvoiceData($type, $order, $iwsOrder) 
    {
        $update = 0;
        try{
            switch($type){
                
                case 'invoice.upload':               
                    $data = $this->getWSData($type, $iwsOrder);
                    //si no tiene info de factura, genera y guarda
                    if(!$iwsOrder->getTraxInvoice() || $this->_simulate['validate']){
                        $comment = $this->_cdiHelper->getCommentByStatus($data, 'invoice');
                        $this->_cdiHelper->addOrderComment(
                            $order, 
                            $comment['msg'],
                            $comment['notify'],
                            $comment['newstatus']
                        );
                        $iwsOrder->setTraxInvoice(serialize($data));
                        $iwsOrder->save();
                        $this->logger->info('Mienviowebhook - Se actualizo la orden : '.$iwsOrder->getId());
                    }
                    break;
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->info('Mienviowebhook - Error al actualizar la orden con id: '.$iwsOrder->getId());
            throw $e;
        }
        return false;
    }
}
