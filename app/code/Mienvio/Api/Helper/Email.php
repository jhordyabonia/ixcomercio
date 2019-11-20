<?php
namespace Mienvio\Api\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Email extends AbstractHelper
{
    const XML_PATH_EMAIL_TEMPLATE_FIELD = 'trax_catalogo/catalogo_general/template_notification';

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
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
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
     * @param $name
     * @param $email
     * @return $this
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function notify($name, $email, $reintentos, $serviceUrl, $payload, $storeid)
    {

        /* Receiver Detail */
        $receiverInfo = [
            'name' => $name,
            'email' => $email
        ];
        
        /* Sender Detail  */
        $senderInfo = [
            'name' => 'Whitelabel Store',
            'email' => 'sender@addess.com',
        ];

        /* Assign values for your template variables  */
        $variable = [];
        $variable['reintentos'] = $reintentos;
        $variable['serviceUrl'] = $serviceUrl;
        $variable['payload'] = $payload;

        $templateId = "trax_catalogo_catalogo_general_template_notification";
        $this->inlineTranslation->suspend();
        $this->generateTemplate($variable, $receiverInfo, $senderInfo, $templateId, $storeid);
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();

        return $this;
    }
}