<?php
namespace Intcomex\Credomatic\Model;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\DataObject;
$instalmments = null;
$device_fingerprint = null;
class Payment extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'Credomatic';
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
    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];
    protected $_credomaticLogger;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Intcomex\Credomatic\Logger\Logger $credomaticLogger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = array()
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
            $data
        );
        $this->_countryFactory = $countryFactory;
        $this->_localeDate = $localeDate;
        $this->_messageManager = $messageManager;
        $this->_credomaticLogger = $credomaticLogger;
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
    global $credomatic_data;
    $credomatic_data = $additionalData->getData();
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
       /* $payment->setAdditionalInformation('visacuotas',$this->getConfigData('visacuotas'));*/
       /* $payment->setAdditionalInformation('requiredInvoice',$this->getConfigData('MerchantId'));*/
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

            $customError = (string) $this->getConfigData('CustomErrorMsg');
            $showCustomError = false;
            if($customError != '') {
                $showCustomError = true;
            }

            $time = strtotime(date('Y-m-d H:i:s'));
            global $installments;

            $order = $payment->getOrder();

            $hash = md5($order->getIncrementId().'|'.$amount.'.00'.'|'.$time.'|'.$this->getConfigData('key'));
            $data = array();
            $data['type'] = 'sale';
            $data['key_id'] = $this->getConfigData('key_id');
            $data['hash'] = $hash;
            $data['time'] = $time;
            /*$data['redirect'] = 'Sale';*/
            $data['ccnumber'] = $payment->getCcNumber();
            $data['ccexp'] = str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT).substr($payment->getCcExpYear(), 2, 4);
            $data['amount'] = $amount.'.00';
            $data['orderid'] = $order->getIncrementId();
            $data['cvv'] = $payment->getCcCid();
            /*$data['avs'] = 'Sale';*/
            $data['processor_id'] = $this->getConfigData('processor_id'.$installments);
            
            $data_string = '';
            foreach($data as $key => $value){
                $data_string .= $key.'='.$value.'&';
            }
            $data_string = rtrim($data_string, '&');

            $this->_credomaticLogger->info('Log de datos');
            $this->_credomaticLogger->info(print_r($data,true));
           
            $service_url = 'https://credomatic.compassmerchantsolutions.com/api/transact.php';
        
            $host = $service_url;
            $ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, $host);
			// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			// curl_setopt($process, CURLOPT_USERPWD, $username_pse . ":" . $password_pse);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			// curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_POST, 1);
			// curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$errors = curl_error($ch);                                                                                                            
			$result = curl_exec($ch);
            
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($result, 0, $header_size);
            curl_close($ch); 
            $body = $this->decodeBody(substr($result, $header_size));

            $this->_credomaticLogger->info('Response');
            $this->_credomaticLogger->info(print_r($header,true));
            $this->_credomaticLogger->info(print_r($body,true));
            
            if($body['response_code']==300||$body['response_code']==200){
                
                if( $showCustomError ) {
                    $this->_messageManager->addErrorMessage($customError);
                    throw new \Magento\Framework\Exception\LocalizedException(__($customError));
                }else {
                   $errorMsg = $body['responsetext'];
                   $this->_messageManager->addErrorMessage($errorMsg); 
                   throw new \Magento\Framework\Exception\LocalizedException(__($errorMsg));
                }
            }else if($body['response_code']==100){
                $payment->setLastTransId($body['transactionid']);
            }

        } catch (\Exception $e) {
            $this->debugData(['request' => $data, 'exception' => $e->getMessage()]);
            $error = __('Payment capturing error Credomatic: '); 
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
        if (!$this->getConfigData('key_id') || !$this->getConfigData('key')) {
            return false;
        }
        return parent::isAvailable($quote);
    }

    public function decodeBody($query){
        $dataArray = array();
        foreach (explode('&', $query) as $chunk) {
            $param = explode("=", $chunk);
        
            if ($param) {
                $dataArray[$param[0]] = $param[1];
            }
        }
        return $dataArray;
    }
}
