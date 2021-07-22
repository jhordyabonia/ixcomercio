<?php
namespace Intcomex\Credomatic_MasterCard\Model;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\DataObject;
$instalmments = null;
$device_fingerprint = null;
class Payment extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'Credomatic_mastercard';
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
        \Intcomex\Credomatic_MasterCard\Logger\Logger $credomaticLogger,
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