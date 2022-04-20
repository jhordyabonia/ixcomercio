<?php
namespace Magento\PagaloVisa\Model;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\DataObject;
$instalmments = null;
$device_fingerprint = null;
class Payment extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'pagalovisa';
    protected $_code = self::CODE;
    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_stripeApi = false;
    protected $_countryFactory;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array('USD');
    //private $installments = 3;
    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];
    protected $_pagaloLogger;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\PagaloVisa\Logger\Logger $pagaloLogger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = array(),
        string $merchantId = ""
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data,
            $merchantId
        );
        $this->_countryFactory = $countryFactory;
        $this->_merchantId = $this->getConfigData('MerchantId');
        $this->_localeDate = $localeDate;
        $this->_messageManager = $messageManager;
        $this->_pagaloLogger = $pagaloLogger;
    }
    public function assignData(\Magento\Framework\DataObject $data){
	$additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_object($additionalData)) {
            $additionalData = new DataObject($additionalData ?: []);
        }
        /** @var DataObject $info */
        $info = $this->getInfoInstance();
	global $installments;
	global $device_fingerprint;
    global $pagalo_data;
    $pagalo_data = $additionalData->getData();
	$installments = $additionalData->getData('cc_installments'); 
	$device_fingerprint = $additionalData->getData('cc_fingerprint');
	$additionalData->getData('cc_installments');
        $info->addData(
            [
                'cc_type' => $additionalData->getCcType(),
                'cc_owner' => $additionalData->getCcOwner(),
                'cc_last_4' => substr($additionalData->getCcNumber(), -4),
                'cc_number' => $additionalData->getCcNumber(),
                'cc_cid' => $additionalData->getCcCid(),
                'cc_exp_month' => $additionalData->getCcExpMonth(),
                'cc_exp_year' => $additionalData->getCcExpYear(),
                'cc_ss_issue' => $additionalData->getCcSsIssue(),
                'cc_ss_start_month' => $additionalData->getCcSsStartMonth(),
                'cc_ss_start_year' => $additionalData->getCcSsStartYear(),
		        'cc_installments' => $additionalData->getData('cc_installments'),
            ]
        );
        return $this;
    }
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $payment->setAdditionalInformation('payment_type', $this->getConfigData('payment_action'));
        $payment->setAdditionalInformation('visacuotas',$this->getConfigData('visacuotas'));
        $payment->setAdditionalInformation('requiredInvoice',$this->getConfigData('MerchantId'));
    }    
    /**
     * Payment capturing
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $order = $payment->getOrder();
            $billing = $order->getBillingAddress();
            $currency = strtolower($order->getBaseCurrencyCode());
            $card_number = $payment->getCcNumber();
            $exp_month = $payment->getCcExpMonth();
            $exp_year = $payment->getCcExpYear();
            $cvc = $payment->getCcCid();
            $card_name = $billing->getName();
            $pg_token = $this->getConfigData('APIToken');
            if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                //check ip from share internet
                $pg_ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                //to check ip is pass from proxy
                $pg_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $pg_ip = $_SERVER['REMOTE_ADDR'];
            }
    	    $payment->getAdditionalInformation();
            // cc data
            $pg_nameCard = $billing->getName();
            $pg_accountNumber = str_replace(' ', '', $payment->getCcNumber()); 
            $pg_expirationMonth = str_replace(' ', '',sprintf('%02d',$payment->getCcExpMonth()));
            $pg_expirationYear = str_replace(' ', '', $payment->getCcExpYear());
            $pg_CVVCard = $payment->getCcCid();
            global $installments, $device_fingerprint, $pagalo_data, $installments, $pagalo_data2;
            $pagalo_data2 = $payment->getAdditionalInformation();
            $empresa= array(
                'key_secret'=> $this->getConfigData('APISecret'),
                'key_public'=> $this->getConfigData('APIKey'),
                'idenEmpresa'=> $this->getConfigData('BusinessId'),
            );
            $str_empresa = json_encode($empresa);
            $cliente= array(
                'firstName' => html_entity_decode($billing->getFirstname(), ENT_QUOTES, 'UTF-8'),
                'lastName' => html_entity_decode($billing->getLastname(), ENT_QUOTES, 'UTF-8'),
                'street1'=> html_entity_decode($billing->getStreetLine(1), ENT_QUOTES, 'UTF-8'),
                'phone'=> $billing->getTelephone(),
                'country'=> html_entity_decode($billing->getCountryId(), ENT_QUOTES, 'UTF-8'),
                'city'=> html_entity_decode($billing->getCity(), ENT_QUOTES, 'UTF-8'),
                'state'=> html_entity_decode($billing->getRegion(), ENT_QUOTES, 'UTF-8'),
                'postalCode'=> html_entity_decode($billing->getPostcode(), ENT_QUOTES, 'UTF-8'),
                'email'=> $order->getCustomerEmail(),
                'ipAddress'=> $pg_ip,
                'Total'=> $order->getGrandTotal(),
                'fecha_transaccion'=> $this->_localeDate->date()->format('Y-m-d H:i:s'),
                'currency'          => $order->getBaseCurrencyCode(),
                'deviceFingerprintID' => $device_fingerprint,
            );
            $str_cliente = json_encode($cliente);
            $pg_products =  $order->getAllVisibleItems();   
            $detalle=[];
            $detalle_nombre = "";
            foreach ( $pg_products as $pg_product ) {
                if (!$pg_product->getData('has_children')) {       
                        $detalle_nombre .= $pg_product->getName();
                        $detalle_nombre .= "-Q" . $pg_product->getPrice();
                        $detalle_nombre .= "x" . $pg_product->getQtyOrdered() . ",";             
                }
            }
            if ( $order->getShippingAmount() > 0 ) {
                $detalle_nombre .= 'envio';
                $detalle_nombre .= "-Q" . $order->getShippingAmount();
                $detalle_nombre .= "x1";
            }
            $detalle[] = array(
                'id_producto'   => 'product01',
                'cantidad'      => '1',
                'tipo'              => 'product',
                'nombre'            => $order->getIncrementId().' | '.$detalle_nombre,
                'precio'            => $order->getGrandTotal(),
                'Subtotal'      => $order->getGrandTotal(),
            );
            $str_detalle = json_encode($detalle);
            $url = 'https://app.pagalocard.com/api/v1/integracion/' . $pg_token;
            if ($this->getConfigData('PGModalidad') == 'EPAY' ) {
                $url = 'https://app.pagalocard.com/api/v1/integracionpg/' . $pg_token;
            }
            $tarjetaPagalo = array(
                    'nameCard'=> $pg_nameCard,
                    'accountNumber'=> $pg_accountNumber,
                    'expirationMonth'=> $pg_expirationMonth,
                    'expirationYear'=>  $pg_expirationYear,
                    'CVVCard'=> $pg_CVVCard,                        
            );
            if ($installments > 1) {
                $tarjetaPagalo = array(
                    'nameCard'=> $pg_nameCard,
                    'accountNumber'=> $pg_accountNumber,
                    'expirationMonth'=> $pg_expirationMonth,
                    'expirationYear'=>  $pg_expirationYear,
                    'CVVCard'=> $pg_CVVCard,
                    'nCuotas'=> $installments,
                );    
                $url = 'https://app.pagalocard.com/api/v1/integracionpg/' . $pg_token;
            }
            $str_tarjeta = json_encode($tarjetaPagalo);
            $data = array(
                'empresa' => $str_empresa,
                'cliente' => $str_cliente,
                'detalle' => $str_detalle,
                'tarjetaPagalo' => $str_tarjeta
            );
            $str_data = json_encode($data);
            $tarjetaPagalo_debug = array(
                    'accountNumber'=>  '**** **** **** ' . substr($pg_accountNumber, -4),
                    'expirationMonth'=> $pg_expirationMonth,
                    'expirationYear'=>  $pg_expirationYear,
            );  
            if ($installments > 1) {
                $tarjetaPagalo_debug = array(
                    'accountNumber'=>  '**** **** **** ' . substr($pg_accountNumber, -4),
                    'expirationMonth'=> $pg_expirationMonth,
                    'expirationYear'=>  $pg_expirationYear,
                    'nCuotas'=> $installments,
                );
            }
            $str_tarjeta_debug = json_encode($tarjetaPagalo_debug);
            $debug_data = array(
                'empresa' => $str_empresa,
                'cliente' => $str_cliente,
                'detalle' => $str_detalle,
                'tarjetaPagalo' => $str_tarjeta_debug
            );
            $ch = curl_init($url);
            //Tell cURL that we want to send a POST request.
            curl_setopt($ch, CURLOPT_POST, 1);
            //Attach our encoded JSON string to the POST fields.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $str_data);
            //Set the content type to application/json
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //Execute the request
            $result = curl_exec($ch);
            $result = json_decode($result);
            $json = array();
            //var_dump($response->responseCode);
            /* Logs to var/log/pagalovisa.log */
            $this->_pagaloLogger->info('Data sent to Pagalo: ' . json_encode($debug_data, JSON_PRETTY_PRINT) );
            $this->_pagaloLogger->info('URL: ' . $url );
            $this->_pagaloLogger->info('Pagalo Response: ' . print_r($result, true) ); 
            
            if(isset($result->infotran)){
                if(isset($result->infotran->authorizationNumber)){
                    $payment->setLastTransId($result->infotran->authorizationNumber);
                    $this->_pagaloLogger->info('setLastTransId (authorizationNumber): ' . $payment->getLastTransId());
                }
            } else if(isset($result->transaccion)) {
                $payment->setLastTransId($result->transaccion);
                $this->_pagaloLogger->info('setLastTransId (transaccion): ' . $payment->getLastTransId());
            }

            $errorMsg = '';
            $errorMsg_desc = '';
            $customError = (string) $this->getConfigData('PGCustomErrorMsg');
            $showCustomError = false;
            if($customError != '') {
                $showCustomError = true;
            }

            if(property_exists($result, 'reasonCode')) {
                if($result->reasonCode != '00' && $result->reasonCode != '100'){
                    $errorMsg = "Error al procesar el pago. ";
                    if(property_exists($result, 'reasonCode')) {
                        switch ($result->reasonCode) {
                            case '101':
                                $errorMsg .= "Transacción rechazada, falta uno o dos campos en la solicitud. ";
                                break;
                            case '102':
                                $errorMsg .= "Datos de la solicitidud invalidos. ";
                                break;
                            case '104':
                                $errorMsg .= "Transacción rechazada, intente nuevamente. ";
                                break;
                            case '110':
                                $errorMsg .= "Transacción no aprobada, intente nuevamente. ";
                                break;
                            case '150':
                                $errorMsg .= "Transacción invalida, contacte a soporte. ";
                                break;
                            case '151':
                                $errorMsg .= "Time out. ";
                                break;
                            case '152':
                                $errorMsg .= "Time out. Contacte a soporte. ";
                                break;
                            case '200':
                                $errorMsg .= "Transacción rechazada, contacte a soporte. ";
                                break;
                            case '201':
                                $errorMsg .= "Transacción rechazada, contacte a soporte. ";
                                break;
                            case '202':
                                $errorMsg .= "Tarjeta vencida ó fecha de la tarjeta invalida. ";
                                break;
                            case '203':
                                $errorMsg .= "Transacción rechazada, contate a su banco. ";
                                break;
                            case '204':
                                $errorMsg .= "Fondos insuficientes. ";
                                break;
                            case '205':
                                $errorMsg .= "Tarjeta reportada como robada o perdida. ";
                                break;
                            case '207':
                                $errorMsg .= "Transacción rechazada, contacte a su banco. ";
                                break;
                            case '208':
                                $errorMsg .= "La tarjeta o tarjeta inactiva no está autorizada para transacciones que no están presentes en la tarjeta. ";
                                break;
                            case '209':
                                $errorMsg .= "CVV no valido. ";
                                break;
                            case '210':
                                $errorMsg .= "Fondos insuficientes. ";
                                break;
                            case '211':
                                $errorMsg .= "CVV no valido. ";
                                break;
                            case '220':
                                $errorMsg .= "Transacción rechazada, intente nuevamente si persiste contate a soporte. ";
                                break;
                            case '221':
                                $errorMsg .= "Transacción invalida. Contacte a soporte. ";
                                break;
                            case '222':
                                $errorMsg .= "Transacción rechazada. Contacte a su banco. ";
                                break;
                            case '230':
                                $errorMsg .= "Trate nuevamente, el sistema no reconocio CVV. ";
                                break;
                            case '231':
                                $errorMsg .= "Número de tarjeta invalido ";
                                break;
                            case '232':
                                $errorMsg .= "Tipo de tarjeta no valida, intente con otra tarjeta o contacte a soporte para detalles. ";
                                break;
                            case '233':
                                $errorMsg .= "Transacción invalida. Intente nuevamente. ";
                                break;
                            case '234':
                                $errorMsg .= "Credenciales invalidas, contacte a soporte. ";
                                break;
                            case '235':
                                $errorMsg .= "Fondos insuficientes. ";
                                break;
                            case '236':
                                $errorMsg .= "Transacción invalida, contacte a soporte. ";
                                break;
                            case '237':
                                $errorMsg .= "Transacción invalida, contacte a soporte. ";
                                break;
                            case '238':
                                $errorMsg .= "Transacción invalida, contacte a soporte. ";
                                break;
                            case '240':
                                $errorMsg .= "Tarjeta invalida. ";
                                break;
                            case '250':
                                $errorMsg .= "Time out.";
                                break;
                            case '251':
                                $errorMsg .= "Insuficiente información del cliente/dirección. ";
                                break;
                            case '254':
                                $errorMsg .= "Transacción invalida, contacte a soporte. ";
                                break;
                            case '461':
                                $errorMsg .= "Datos no validos, contacte a soporte. ";
                                break;
                            case '481':
                                $errorMsg .= "Transacción rechazada posiblemente por varios intentos, contacte a soporte para mas detalles. ";
                                break;
                            case '00':
                                $errorMsg .= "Transacción aprobada.";
                                break;
                            case '01':
                                $errorMsg .= "Contacte a su banco emisor.";
                                break;
                            case '02':
                                $errorMsg .= "Contacte a su banco emisor.";
                                break;
                            case '03':
                                $errorMsg .= "Credenciales invalidas, contacte a soporte.";
                                break;
                            case '04':
                                $errorMsg .= "Contacte a su banco emisor.";
                                break;
                            case '05':
                                $errorMsg .= "Contacte a su banco emisor. Posible visacuota sin permisos del banco emisor.";
                                break;
                            case '07':
                                $errorMsg .= "Transacción rechazada, retener tarjeta.";
                                break;
                            case '12':
                                $errorMsg .= "Transacción invalida, intente nuevamente.";
                                break;
                            case '13':
                                $errorMsg .= "Fondos insuficientes.";
                                break;
                            case '14':
                                $errorMsg .= "Número de tarjeta invalido.";
                                break;
                            case '15':
                                $errorMsg .= "Credenciales invalidas, contacte a soporte.";
                                break;
                            case '19':
                                $errorMsg .= "Intenta nuevamente.";
                                break;
                            case '25':
                                $errorMsg .= "Credenciales invalidas, contacte a soporte.";
                                break;
                            case '30':
                                $errorMsg .= "Faltan datos obligatorios que enviar, contacte a soporte.";
                                break;
                            case '31':
                                $errorMsg .= "Error al validar campos de la tarjeta";
                                break;
                            case '35':
                                $errorMsg .= "Tarjeta invalida.";
                                break;
                            case '36':
                                $errorMsg .= "Transacción invalida, intente nuevamente.";
                                break;
                            case '41':
                                $errorMsg .= "Tarjeta reportada como perdida o robada.";
                                break;
                            case '43':
                                $errorMsg .= "Tarjeta reportada como perdida o robada.";
                                break;
                            case '51':
                                $errorMsg .= "Fondos insuficientes.";
                                break;
                            case '54':
                                $errorMsg .= "Tarjeta de fecha expirada.";
                                break;
                            case '58':
                                $errorMsg .= "Transacción rechazada, intente de nuevo.";
                                break;
                            case '61':
                                $errorMsg .= "Fondos insuficientes.";
                                break;
                            case '62':
                                $errorMsg .= "Tarjeta sin permisos, contacte su banco emisor.";
                                break;
                            case '65':
                                $errorMsg .= "Transacción invalida, contacte a soporte. Monto de afiliación.";
                                break;
                            case '78':
                                $errorMsg .= "Credenciales invalidas, contacte a soporte.";
                                break;
                            case '85':
                                $errorMsg .= "Transacción invalida, intente nuevamente.";
                                break;
                            case '89':
                                $errorMsg .= "Credenciales invalidas, contacte a soporte.";
                                break;
                            case '91':
                                $errorMsg .= "Emisor NO Disponible - TIME OUT";
                                break;
                            case '96':
                                $errorMsg .= "Transacción rechazada, contacte a soporte.";
                                break;
                            default:
                               $errorMsg .= "Contacte al administrador de la tienda para más información o formas alternativas de pago.";
                        }
                    }
                    if(property_exists($result, 'mensaje')) {
                        $errorMsg .= "Mensaje: " . $result->mensaje . '. ';
                    }
                    if($showCustomError) {
                        $this->_messageManager->addErrorMessage($customError);
                        throw new \Magento\Framework\Exception\LocalizedException(__($customError));
                    }
                    else {
                        $this->_messageManager->addErrorMessage($errorMsg);
                        throw new \Magento\Framework\Exception\LocalizedException(__($errorMsg));
                    }
                    throw new \Magento\Framework\Exception\LocalizedException(__($customError));
                }
            }
            elseif(property_exists($result, 'codigo')) {
                    $errorMsg = "Error al procesar el pago. ";
                    if(property_exists($result, 'mensaje')) {
                        $errorMsg .= $result->mensaje . '. ';
                    }
                    if(property_exists($result, 'descripcion')) {
                        $errorMsg .= $result->descripcion . '. ';
                    }
                    if( $showCustomError ) {
                        $this->_messageManager->addErrorMessage($customError);
                        throw new \Magento\Framework\Exception\LocalizedException(__($customError));
                    }
                    else {
                        $this->_messageManager->addErrorMessage($errorMsg); 
                        throw new \Magento\Framework\Exception\LocalizedException(__($errorMsg));
                    }
                    throw new \Magento\Framework\Exception\LocalizedException(__($customError));
            }
            else {
                $errorMsg = __('Servicio de cobros de tarjeta de credito no disponible en este momento. Por favor contacta al administrador de la tienda para mas información y formas alternativas de pago');
                if( $showCustomError ) {
                    $this->_messageManager->addErrorMessage($customError);
                }
                else {
                    $this->_messageManager->addErrorMessage($errorMsg);
                    throw new \Magento\Framework\Exception\LocalizedException(__($errorMsg));
                }
                throw new \Magento\Framework\Exception\LocalizedException(__($customError));
            }

            if ($errorMsg !== '') {
                $this->_pagaloLogger->info('Mensaje de error: ' . $errorMsg . '. Custom error: ' . $customError);
            }
        } catch (\Exception $e) {
            $this->debugData(['request' => $debug_data, 'exception' => $e->getMessage()]);
            $error = __('Payment capturing error pagalo:'); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }
        return $this;
    }
    /**
     * Determine method availability based on quote amount and config data
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote && (
            $quote->getBaseGrandTotal() < $this->_minAmount
            || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }
        if (!$this->getConfigData('BusinessId') || !$this->getConfigData('APIKey') || !$this->getConfigData('APISecret')) {
            return false;
        }
        return parent::isAvailable($quote);
    }
}