<?php

namespace Intcomex\CustomLog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Email extends AbstractHelper
{
    protected $scopeConfig;

    /**
     * Sender email config path - from default CONTACT extension
     */
    const XML_PATH_EMAIL_SENDER = 'contact/email/sender_email_identity';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * Email constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Api\StoreRepositoryInterface $storesRepository
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->_storesRepository = $storesRepository;
    }

    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int $storeid
     * @return mixed
     */
    private function getConfigValue($path, $storeid)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeid
        );
    }

    /**
     * Return store
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateTemplate($variable, $receiverInfo, $senderInfo, $templateId, $storeid)
    {
        $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                    'store' => $storeid,
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
     * @param $email
     * @param $variables
     * @param $templateId
     * @return $this|void
     */
    public function notify($email,$variables,$templateId)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 

        /* Sender Detail  */
        $senderInfo = [
            'name' => 'Whitelabel Store',
            'email' => 'soporteb2c@ixcomercio.com',
        ];

        $receiverInfo = [
            'name' => $email,
            'email' => $email
        ];

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test_email_log.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->logger->info(print_r($variables,true));

        // Assign values for your template variables
        $variable = $variables;
        if(isset($variables['body'])&&$variables['body']!=''){
            $theme = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $storeid = $theme->getStore()->getId();

            try {
                $this->inlineTranslation->suspend();
                $this->generateTemplate($variable, $receiverInfo, $senderInfo, $templateId, $storeid);
                $transport = $this->transportBuilder->getTransport();
                $send = $transport->sendMessage();
                $this->inlineTranslation->resume();
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/validacion_envio_correo.log');
                $this->logger = new \Zend\Log\Logger();
                $this->logger->addWriter($writer);
                $this->logger->info(print_r($send,true));
            } catch (\Exception $e) {
                $this->_logger->info('Error Sending Email: ' . $e->getMessage() . " / Store: $storeid / TemplateId: $templateId");
            }

            return $this;
        }
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
