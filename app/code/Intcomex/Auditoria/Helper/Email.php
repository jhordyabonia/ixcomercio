<?php

namespace Intcomex\Auditoria\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Email extends AbstractHelper
{
    /**
     * Sender email config path - from default CONTACT extension
     */
    const XML_PATH_EMAIL_SENDER = 'contact/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE_FIELD = 'trax_catalogo/catalogo_general/template_notification';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    private function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @param $variable
     * @param $receiverInfo
     * @param $templateId
     * @return $this
     * @throws NoSuchEntityException
     */
    public function generateTemplate($variable, $receiverInfo, $senderInfo, $templateId, $storeId)
    {
        $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                    'store' => $storeId,
                ]
            )
            ->setTemplateVars($variable)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);

        return $this;
    }

    /**
     * Return email for sender header
     * @return mixed
     */
    public function emailSender()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $data
     * @param $websiteCode
     * @param $storeId
     * @param $extraError
     * @return $this
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function notify($data, $websiteCode, $storeId, $extraError = null)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $subject = $this->scopeConfig->getValue('auditoria/price/subject', $storeScope);
        $message = $this->scopeConfig->getValue('auditoria/price/message', $storeScope);
        $emails = explode(',',$this->scopeConfig->getValue('auditoria/price/emails', $storeScope));
        
        /* Sender Detail  */
        $senderInfo = [
            'name' => 'Whitelabel Store',
            'email' => 'soporteb2c@ixcomercio.com',
        ];

        /* Assign values for your template variables  */
        $variable = [];
        $variable['subject'] = $subject;
        $variable['message'] = $message;
        $variable['website'] = $websiteCode;
        $variable['extra_error'] = $extraError;
        $variable['data'] = $data;

        $templateId = "email_auditoria_template";
        foreach($emails as $key => $value){
            if(!empty($value)){
                 /* Receiver Detail */
                $receiverInfo = [
                    'name' => 'Soporte Whitelabel',
                    'email' => $value
                ];

                $this->inlineTranslation->suspend();
                $this->generateTemplate($variable, $receiverInfo, $senderInfo, $templateId, $storeId);
                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            }
        }

        return $this;
    }

    /**
     * @param $websiteCode
     * @param $storeId
     * @return $this
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function notifyCurrrencyErrorEmail($data, $storeId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $subject = $this->scopeConfig->getValue('auditoria/currency/subject', $storeScope);
        $message = $this->scopeConfig->getValue('auditoria/currency/message', $storeScope);
        $emails = explode(',',$this->scopeConfig->getValue('auditoria/currency/emails', $storeScope));

        /* Sender Detail  */
        $senderInfo = [
            'name' => 'Whitelabel Store',
            'email' => 'soporteb2c@ixcomercio.com',
        ];

        /* Assign values for your template variables  */
        $variable = [];
        $variable['subject'] = $subject;
        $variable['message'] = $message;
        $variable['data'] = $data;

        $templateId = "currency_error_template";
        foreach($emails as $key => $value){
            if(!empty($value)){
                /* Receiver Detail */
                $receiverInfo = [
                    'name' => 'Soporte Whitelabel',
                    'email' => $value
                ];

                $this->inlineTranslation->suspend();
                $this->generateTemplate($variable, $receiverInfo, $senderInfo, $templateId, $storeId);
                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            }
        }

        return $this;
    }

    public function clearSpecialCharac($String){
        $String = str_replace(array('á','à','â','ã','ª','ä'),"a",$String);
        $String = str_replace(array('Á','À','Â','Ã','Ä'),"A",$String);
        $String = str_replace(array('Í','Ì','Î','Ï'),"I",$String);
        $String = str_replace(array('í','ì','î','ï'),"i",$String);
        $String = str_replace(array('é','è','ê','ë'),"e",$String);
        $String = str_replace(array('É','È','Ê','Ë'),"E",$String);
        $String = str_replace(array('ó','ò','ô','õ','ö','º'),"o",$String);
        $String = str_replace(array('Ó','Ò','Ô','Õ','Ö'),"O",$String);
        $String = str_replace(array('ú','ù','û','ü'),"u",$String);
        $String = str_replace(array('Ú','Ù','Û','Ü'),"U",$String);
        $String = str_replace(array('^','´','`','¨','~'),"",$String);
        $String = str_replace("ç","c",$String);
        $String = str_replace("Ç","C",$String);
        $String = str_replace("ñ","n",$String);
        $String = str_replace("Ñ","n",$String);
        $String = str_replace("Ý","Y",$String);
        $String = str_replace("ý","y",$String);
        $String = str_replace("&aacute;","a",$String);
        $String = str_replace("&Aacute;","A",$String);
        $String = str_replace("&eacute;","e",$String);
        $String = str_replace("&Eacute;","E",$String);
        $String = str_replace("&iacute;","i",$String);
        $String = str_replace("&Iacute;","I",$String);
        $String = str_replace("&oacute;","o",$String);
        $String = str_replace("&Oacute;","O",$String);
        $String = str_replace("&uacute;","u",$String);
        $String = str_replace("&Uacute;","U",$String);
        return $String;
    }
}
