<?php

/**
 * Pasarela_Bancomer payment method model
 *
 * @category    Bancomer
 * @package     Pasarela_Bancomer
 * @author      Valentina Aguirre
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
 */

namespace Pasarela\Bancomer\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Payment\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;

class Payment extends \Magento\Payment\Model\Method\Cc
{

    const CODE = 'bancomer_multipagos';

    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canAuthorize = true;
    protected $_canVoid = true;
    protected $openpay = false;
    protected $is_sandbox;
    protected $use_card_points;
    protected $merchant_id = null;
    protected $pk = null;
    protected $sk = null;
    protected $sandbox_merchant_id;
    protected $sandbox_sk;
    protected $sandbox_pk;
    protected $live_merchant_id;
    protected $live_sk;
    protected $live_pk;
    protected $country_factory;
    protected $scopeConfig;
    protected $supported_currency_codes = array('USD', 'MXN');    
    protected $months_interest_free;
    protected $charge_type;
    protected $logger;
    protected $_storeManager;
    protected $save_cc;
    
    /**
     * @var Customer
     */
    protected $customerModel;
    /**
     * @var CustomerSession
     */
    protected $customerSession;    
    
    protected $openpayCustomerFactory;


    /**
     * 
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Bancomer $openpay
     * @param array $data
     * @param \Magento\Store\Model\StoreManagerInterface $data
     */
    public function __construct(
            StoreManagerInterface $storeManager,
            Context $context, 
            Registry $registry, 
            ExtensionAttributesFactory $extensionFactory, 
            AttributeValueFactory $customAttributeFactory, 
            Data $paymentData, 
            ScopeConfigInterface $scopeConfig, 
            Logger $logger,
            ModuleListInterface $moduleList, 
            TimezoneInterface $localeDate, 
            CountryFactory $countryFactory, 
            \Openpay $openpay,             
            \Psr\Log\LoggerInterface $logger_interface,            
            Customer $customerModel,
            CustomerSession $customerSession,            
            \Pasarela\Bancomer\Model\BancomerCustomerFactory $openpayCustomerFactory,
            array $data = array()            
    ) {
        
        parent::__construct(
                $context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $moduleList, $localeDate, null, null, $data
        );
                    
        $this->customerModel = $customerModel;
        $this->customerSession = $customerSession;
        $this->openpayCustomerFactory = $openpayCustomerFactory;
        
        $this->_storeManager = $storeManager;
        $this->logger = $logger_interface;

        $this->scopeConfig = $scopeConfig;
        $this->country_factory = $countryFactory;

        $this->is_active = $this->getConfigData('active');
        $this->is_sandbox = $this->getConfigData('is_sandbox');
        $this->sandbox_merchant_id = $this->getConfigData('sandbox_merchant_id');
        $this->sandbox_sk = $this->getConfigData('sandbox_sk');
        $this->sandbox_pk = $this->getConfigData('sandbox_pk');
        $this->live_merchant_id = $this->getConfigData('live_merchant_id');
        $this->live_sk = $this->getConfigData('live_sk');
        $this->live_pk = $this->getConfigData('live_pk');

        $this->merchant_id = $this->is_sandbox ? $this->sandbox_merchant_id : $this->live_merchant_id;
        $this->sk = $this->is_sandbox ? $this->sandbox_sk : $this->live_sk;
        $this->pk = $this->is_sandbox ? $this->sandbox_pk : $this->live_pk;
        $this->months_interest_free = $this->getConfigData('interest_free');
        $this->charge_type = $this->getConfigData('charge_type');
        //$this->minimum_amount = $this->getConfigData('minimum_amount');
        $this->use_card_points = $this->getConfigData('use_card_points');
        $this->save_cc = $this->getConfigData('save_cc');
        
        $this->openpay = $openpay;
    }
    
    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException     
     */
    public function validate() {                                     
        $info = $this->getInfoInstance();
        $bancomer_cc = $info->getAdditionalInformation('bancomer_cc');
        
        $this->logger->debug('#validate', array('$bancomer_cc' => $bancomer_cc));                    
        
        // Si se utiliza una tarjeta nueva, se realiza la validación necesaria por Magento 
        if ($bancomer_cc == 'new') {
            return parent::validate();
        }
                
        return $this;
    }

    /**
     * Assign corresponding data
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data) {
        parent::assignData($data);
                
        $infoInstance = $this->getInfoInstance();
        $additionalData = ($data->getData('additional_data') != null) ? $data->getData('additional_data') : $data->getData();
        
        $infoInstance->setAdditionalInformation('device_session_id', 
            isset($additionalData['device_session_id']) ? $additionalData['device_session_id'] :  null
        );
        $infoInstance->setAdditionalInformation('bancomer_token',     
            isset($additionalData['bancomer_token']) ? $additionalData['bancomer_token'] : null
        );
        $infoInstance->setAdditionalInformation('interest_free',
            isset($additionalData['interest_free']) ? $additionalData['interest_free'] : null
        );
        
        $infoInstance->setAdditionalInformation('use_card_points',
            isset($additionalData['use_card_points']) ? $additionalData['use_card_points'] : null
        );
        
        $infoInstance->setAdditionalInformation('save_cc',
            isset($additionalData['save_cc']) ? $additionalData['save_cc'] : null
        );
        
        $infoInstance->setAdditionalInformation('bancomer_cc',
            isset($additionalData['bancomer_cc']) ? $additionalData['bancomer_cc'] : null
        );
        
        return $this;
    }
    
    /**
     * Refund capture
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount){
        $order = $payment->getOrder();
        $trx_id = $order->getExtOrderId();
        $customer_id = $order->getExtCustomerId();
        
        $this->logger->debug('#refund', array('$trx_id' => $trx_id, '$customer_id' => $customer_id, '$order_id' => $order->getIncrementId(), '$status' => $order->getStatus(), '$amount' => $amount));                    
        
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for refund.'));
        }
        
        try {
            $refundData = array(
                'description' => 'Reembolso',
                'amount' => $amount                
            );

//            $openpay = $this->getBancomerInstance();
//            $charge = $openpay->charges->get($trx_id);
            $charge = $this->getBancomerCharge($trx_id, $customer_id);            
            $charge->refund($refundData);            
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }        
        
        return $this;
    }
    
    
    /**
     * Send authorize request to gateway
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface $payment
     * @param  float $amount
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount) {         
        $order = $payment->getOrder();
        $this->logger->debug('#authorize', array('$order_id' => $order->getIncrementId(), '$status' => $order->getStatus(), '$amount' => $amount));                    
        $payment->setAdditionalInformation('payment_type', $this->getConfigData('payment_action'));
        $payment->setIsTransactionClosed(false);
        $payment->setSkipOrderProcessing(false);
        $this->processCapture($payment, $amount);
        return $this;
    }
    
    /**
     * Send capture request to gateway
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount) {
        $order = $payment->getOrder();                
        $this->logger->debug('#capture', array('$order_id' => $order->getIncrementId(), '$trx_id' => $payment->getLastTransId(), '$status' => $order->getStatus(), '$amount' => $amount));                    
        
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for capture.'));
        }               
        
        $payment->setAmount($amount);
        if(!$payment->getLastTransId()){            
            $this->processCapture($payment, $amount);
        } else {
            $this->captureBancomerTransaction($payment, $amount);
        }        
        
        return $this;
    }
    
    protected function captureBancomerTransaction(\Magento\Payment\Model\InfoInterface $payment, $amount){
        $order = $payment->getOrder();                
        $customer_id = $order->getExtCustomerId();
        
        $this->logger->debug('#captureBancomerTransaction', array('$trx_id' => $payment->getLastTransId(), '$customer_id' => $customer_id, '$order_id' => $order->getIncrementId(), '$status' => $order->getStatus(), '$amount' => $amount));                    
                
        try {
            $order->addStatusHistoryComment("Pago recibido exitosamente")->setIsCustomerNotified(true);            
            $charge = $this->getBancomerCharge($payment->getLastTransId(), $customer_id);
            $captureData = array('amount' => $amount);
            $charge->capture($captureData);

            return $charge;
        } catch (\Exception $e) {
            $this->logger->error('captureBancomerTransaction', array('message' => $e->getMessage(), 'code' => $e->getCode()));
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }        
    }
    
    
    /**     
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function processCapture(\Magento\Payment\Model\InfoInterface $payment, $amount) {
        unset($_SESSION['pdf_url']);
        unset($_SESSION['show_map']);
        unset($_SESSION['bancomer_3d_secure_url']);
        
        $base_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);  // URL de la tienda        
        
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        
        $this->logger->debug('#processCapture', array('charge_type' => $this->charge_type,'$order_id' => $order->getIncrementId(), '$status' => $order->getStatus(), '$amount' => $amount));        

        /** @var \Magento\Sales\Model\Order\Address $billing */
        $billing = $order->getBillingAddress();

        $capture = $this->getConfigData('payment_action') == 'authorize_capture' ? true : false;
        $token = $this->getInfoInstance()->getAdditionalInformation('bancomer_token');
        $device_session_id = $this->getInfoInstance()->getAdditionalInformation('device_session_id');
        $use_card_points = $this->getInfoInstance()->getAdditionalInformation('use_card_points');
        $save_cc = $this->getInfoInstance()->getAdditionalInformation('save_cc');
        $bancomer_cc = $this->getInfoInstance()->getAdditionalInformation('bancomer_cc');
        
        if (!$token && (!$bancomer_cc || $bancomer_cc == 'new')) {
            $msg = 'ERROR 100 Please specify card info';
            throw new \Magento\Framework\Validator\Exception(__($msg));
        }
        
        $this->logger->debug('#processCapture', array('$bancomer_cc' => $bancomer_cc, '$save_cc' => $save_cc, '$device_session_id' => $device_session_id));        
                                
        $customer_data = array(
            'requires_account' => false,
            'name' => $billing->getFirstname(),
            'last_name' => $billing->getLastname(),
            'phone_number' => $billing->getTelephone(),
            'email' => $order->getCustomerEmail()
        );

        if ($this->validateAddress($billing)) {
            $customer_data['address'] = array(
                'line1' => $billing->getStreetLine(1),
                'line2' => $billing->getStreetLine(2),
                'postal_code' => $billing->getPostcode(),
                'city' => $billing->getCity(),
                'state' => $billing->getRegion(),
                'country_code' => $billing->getCountryId()
            );
        }
        
        $charge_request = array(
            'method' => 'card',
            'currency' => strtolower($order->getBaseCurrencyCode()),
            'amount' => $amount,
            'description' => sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),                
            'order_id' => $order->getIncrementId(),
            'source_id' => $token,
            'device_session_id' => $device_session_id,
            'customer' => $customer_data,
            'use_card_points' => $use_card_points,  
            'capture' => $capture
        );

        // Meses sin intereses
        $interest_free = $this->getInfoInstance()->getAdditionalInformation('interest_free');
        if($interest_free > 1){
            $charge_request['payment_plan'] = array('payments' => (int)$interest_free);
        }  

        // 3D Secure
        if ($this->charge_type == '3d') {
            $charge_request['use_3d_secure'] = true;
            $charge_request['redirect_url'] = $base_url.'openpay/payment/success';
        }
                
        try {                           
            // Realiza la transacción en Bancomer
            $charge = $this->makeBancomerCharge($customer_data, $charge_request, $token, $device_session_id, $save_cc, $bancomer_cc);                                               
            
            $payment->setTransactionId($charge->id);  
            if(isset($charge->card)){
                $payment->setCcLast4(substr($charge->card->card_number, -4));
                $payment->setCcType($this->getCCBrandCode($charge->card->brand));
                $payment->setCcExpMonth($charge->card->expiration_month);
                $payment->setCcExpYear($charge->card->expiration_year);
            }
                                                                                    
            if ($this->charge_type == '3d') {            
                $payment->setIsTransactionPending(true);                
                $_SESSION['bancomer_3d_secure_url'] = $charge->payment_method->url;
                $this->logger->debug('3d_direct', array('redirect_url' => $charge->payment_method->url, 'bancomer_id' => $charge->id, 'bancomer_status' => $charge->status));
            }
            
            $openpayCustomerFactory = $this->customerSession->isLoggedIn() ? $this->hasBancomerAccount($this->customerSession->getCustomer()->getId()) : null;
            $bancomer_customer_id = $openpayCustomerFactory ? $openpayCustomerFactory->bancomer_id : null;
            
            // Registra el ID de la transacción de Bancomer
            $order->setExtOrderId($charge->id);   
            
            // Registra (si existe), el ID de Customer de Bancomer
            $order->setExtCustomerId($bancomer_customer_id);
            $order->save();                       
            
            $this->logger->debug('#saveOrder');        
            
        } catch (\BancomerApiTransactionError $e) {                        
            $this->logger->error('BancomerApiTransactionError', array('message' => $e->getMessage(), 'code' => $e->getErrorCode(), '$status' => $order->getStatus()));
            
            // Si hubo riesgo de fraude y el usuario definió autenticación selectiva, se envía por 3D secure
            if ($this->charge_type == 'auth' && $e->getErrorCode() == '3005') {                                                
                $charge_request['use_3d_secure'] = true;
                $charge_request['redirect_url'] = $base_url.'openpay/payment/success';
                                
                $charge = $this->makeBancomerCharge($customer_data, $charge_request, $token, $device_session_id, $save_cc, $bancomer_cc);
                $openpayCustomerFactory = $this->customerSession->isLoggedIn() ? $this->hasBancomerAccount($this->customerSession->getCustomer()->getId()) : null;
                                
                $order->setExtOrderId($charge->id);
                $order->setExtCustomerId($openpayCustomerFactory->bancomer_id);
                $order->save();
                
                $payment->setTransactionId($charge->id);      
                $payment->setCcLast4(substr($charge->card->card_number, -4));
                $payment->setCcType($this->getCCBrandCode($charge->card->brand));
                $payment->setCcExpMonth($charge->card->expiration_month);
                $payment->setCcExpYear($charge->card->expiration_year);                
                $payment->setAdditionalInformation('bancomer_3d_secure_url', $charge->payment_method->url); 
                $payment->setSkipOrderProcessing(true);                      
                $payment->setIsTransactionPending(true);
                
                $_SESSION['bancomer_3d_secure_url'] = $charge->payment_method->url;
                
                $this->logger->debug('3d_auth', array('redirect_url' => $charge->payment_method->url, 'bancomer_id' => $charge->id, 'bancomer_status' => $charge->status, '$status' => $order->getStatus()));                
            } else {
                throw new \Magento\Framework\Validator\Exception(__($this->error($e)));
            }
        } catch (\Exception $e) {
            $this->_logger->error(__('Payment capturing error.'));            
            $this->logger->error('ERROR', array('message' => $e->getMessage(), 'code' => $e->getCode()));
            throw new \Magento\Framework\Validator\Exception(__($this->error($e)));
        }
        
        return $this;
    }
    
    private function getCCBrandCode($brand) {
        $code = null;
        switch ($brand) {
            case "mastercard":
                $code = "MC";
                break;
            
            case "visa":
                $code = "VI";
                break;
            
            case "american_express":
                $code = "AE";
                break;
                    
        }
        return $code;
    }
    
    private function makeBancomerCharge($customer_data, $charge_request, $token, $device_session_id, $save_cc, $bancomer_cc) {        
        $openpay = $this->getBancomerInstance();

        if (!$this->customerSession->isLoggedIn()) {
            // Cargo para usuarios "invitados"
            return $openpay->charges->create($charge_request);
        }

        // Se remueve el atributo de "customer" porque ya esta relacionado con una cuenta en Bancomer
        unset($charge_request['customer']); 

        $bancomer_customer = $this->retrieveBancomerCustomerAccount($customer_data);

        if ($save_cc == '1' && $bancomer_cc == 'new') {
            $card_data = array(            
                'token_id' => $token,            
                'device_session_id' => $device_session_id
            );
            $card = $this->createCreditCard($bancomer_customer, $card_data);

            // Se reemplaza el "source_id" por el ID de la tarjeta
            $charge_request['source_id'] = $card->id;                                                            
        } else if ($save_cc == '0' && $bancomer_cc != 'new') {
            $charge_request['source_id'] = $bancomer_cc;                    
        }

        // Cargo para usuarios con cuenta
        return $bancomer_customer->charges->create($charge_request);            
    }
    
    public function getBancomerCharge($charge_id, $customer_id = null) {
        try {                        
            if ($customer_id === null) {                
                $openpay = $this->getBancomerInstance();
                return $openpay->charges->get($charge_id);
            }            
            
            $bancomer_customer = $this->getBancomerCustomer($customer_id);
            return $bancomer_customer->charges->get($charge_id);            
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }
    }
    
    private function hasBancomerAccount($customer_id) {        
        try {
            $bancomer_customer_local = $this->openpayCustomerFactory->create();
            $response = $bancomer_customer_local->fetchOneBy('customer_id', $customer_id);
            return $response;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }  
    }
    
    private function retrieveBancomerCustomerAccount($customer_data) {
        try {
            $customerId = $this->customerSession->getCustomer()->getId();                
            //$customer = $this->customerModel->load($customerId);                
            //$this->logger->debug('getFirstname => '.$customer->getFirstname()); 
            $has_bancomer_account = $this->hasBancomerAccount($customerId);
            if ($has_bancomer_account === false) {
                $bancomer_customer = $this->createBancomerCustomer($customer_data);
                $this->logger->debug('$bancomer_customer => '.$bancomer_customer->id);

                $data = [
                    'customer_id' => $customerId,
                    'bancomer_id' => $bancomer_customer->id
                ];

                // Se guarda en BD la relación
                $bancomer_customer_local = $this->openpayCustomerFactory->create();
                $bancomer_customer_local->addData($data)->save();                    
            } else {
                $bancomer_customer = $this->getBancomerCustomer($has_bancomer_account->bancomer_id);
            }
            
            return $bancomer_customer;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }
    }
    
    private function createBancomerCustomer($data) {
        try {
            $openpay = $this->getBancomerInstance();
            return $openpay->customers->add($data);            
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }        
    }
    
    public function getBancomerCustomer($bancomer_customer_id) {
        try {
            $openpay = $this->getBancomerInstance();
            return $openpay->customers->get($bancomer_customer_id);            
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }        
    }
    
    private function createCreditCard($customer, $data) {
        try {
            return $customer->cards->add($data);            
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }        
    }
    
    private function getCreditCards($customer, $customer_created_at) {
        $from_date = date('Y-m-d', strtotime($customer_created_at));
        $to_date = date('Y-m-d');
        try {
            return $customer->cards->getList(array(
                'creation[gte]' => $from_date,
                'creation[lte]' => $to_date,
                'offset' => 0,
                'limit' => 10
            ));            
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }        
    }
    
    public function getCreditCardList() {
        if (!$this->customerSession->isLoggedIn()) {
            return array(array('value' => 'new', 'name' => 'Nueva tarjeta'));
        }
        
        $customerId = $this->customerSession->getCustomer()->getId();
        $has_bancomer_account = $this->hasBancomerAccount($customerId);
        if ($has_bancomer_account === false) {
            return array(array('value' => 'new', 'name' => 'Nueva tarjeta'));
        }
        
        try {
            $list = array(array('value' => 'new', 'name' => 'Nueva tarjeta'));
            $customer = $this->getBancomerCustomer($has_bancomer_account->bancomer_id);
            $cards = $this->getCreditCards($customer, $has_bancomer_account->created_at);
            
            foreach ($cards as $card) {                
                array_push($list, array('value' => $card->id, 'name' => strtoupper($card->brand).' '.$card->card_number));
            }
            
            return $list;            
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }        
    }
    
    public function isLoggedIn() {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Determine method availability based on quote amount and config data
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null) {
        return parent::isAvailable($quote);
    }

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode) {
        if (!in_array($currencyCode, $this->supported_currency_codes)) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getMerchantId() {
        return $this->merchant_id;
    }

    /**
     * @return string
     */
    public function getPublicKey() {
        return $this->pk;
    }
    
    public function getPrivateKey() {
        return $this->sk;
    }

    /**
     * @return boolean
     */
    public function isSandbox() {
        return $this->is_sandbox;
    }
    
//    public function getMinimumAmount() {
//        return $this->minimum_amount;
//    }
    
    public function getCode() {
        return $this->_code;
    }
    
    public function getBancomerInstance() {
        $openpay = \Bancomer::getInstance($this->merchant_id, $this->sk);
        \Bancomer::setSandboxMode($this->is_sandbox);        
        return $openpay;
    }
    
    public function getMonthsInterestFree() {
        $months = explode(',', $this->months_interest_free);                  
        if(!in_array('1', $months)) {            
            array_unshift($months, '1');
        }        
        return $months;
    }
    
    public function useCardPoints() {
        return $this->use_card_points;
    }
    
    /**
     * 
     * Valida que los clientes pueda guardar sus TC
     * 
     * @return boolean
     */
    public function canSaveCC() {
        return $this->save_cc;
    }
    
    /**
     * @param Exception $e
     * @return string
     */
    public function error($e) {
        /* 6001 el webhook ya existe */
        switch ($e->getCode()) {
            case '1000':
            case '1004':
            case '1005':
                $msg = 'Servicio no disponible.';
                break;
            /* ERRORES TARJETA */
            case '3001':
            case '3004':
            case '3005':
            case '3007':
                $msg = 'La tarjeta fue rechazada.';
                break;
            case '3002':
                $msg = 'La tarjeta ha expirado.';
                break;
            case '3003':
                $msg = 'La tarjeta no tiene fondos suficientes.';
                break;
            case '3006':
                $msg = 'La operación no esta permitida para este cliente o esta transacción.';
                break;
            case '3008':
                $msg = 'La tarjeta no es soportada en transacciones en línea.';
                break;
            case '3009':
                $msg = 'La tarjeta fue reportada como perdida.';
                break;
            case '3010':
                $msg = 'El banco ha restringido la tarjeta.';
                break;
            case '3011':
                $msg = 'El banco ha solicitado que la tarjeta sea retenida. Contacte al banco.';
                break;
            case '3012':
                $msg = 'Se requiere solicitar al banco autorización para realizar este pago.';
                break;
            default: /* Demás errores 400 */
                $msg = 'La petición no pudo ser procesada.';
                break;
        }

        return 'ERROR '.$e->getCode().'. '.$msg;
    }

    /**
     * @param Address $billing
     * @return boolean
     */
    public function validateAddress($billing) {
        if ($billing->getStreetLine(1) && $billing->getCity() && $billing->getPostcode() && $billing->getRegion() && $billing->getCountryId()) {
            return true;
        }
        return false;
    }

}
