<?php

namespace Intcomex\Clearsale\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Email extends AbstractHelper
{
    /**
     * @const Template Transaction Validation.
     */
    const TEMPLATE_TRANSACTION_VALIDATION = 'transaction_validation';

    /**
     * @const Path Email Transaction Validation Subject.
     */
    const PATH_TRANSACTION_VALIDATION_SUBJECT = 'clearsale_configuration/cs_config/transaction_validation_subject';

    /**
     * @const Path Email Transaction Validation Text.
     */
    const PATH_TRANSACTION_VALIDATION_TEXT = 'clearsale_configuration/cs_config/transaction_validation_text';

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * @param Order $order
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendTransactionInValidationMail(Order $order)
    {
        $transport = [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'store' => $order->getStore(),
            'transaction_validation_subject' => $this->scopeConfig->getValue(self::PATH_TRANSACTION_VALIDATION_SUBJECT, ScopeInterface::SCOPE_STORE, $order->getStoreId()),
            'transaction_validation_text' => $this->scopeConfig->getValue(self::PATH_TRANSACTION_VALIDATION_TEXT, ScopeInterface::SCOPE_STORE, $order->getStoreId()),
//            'payment_html' => $this->getPaymentHtml($order),
//            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
//            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
        ];
        $transportObject = new DataObject($transport);
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier(self::TEMPLATE_TRANSACTION_VALIDATION)
            ->setTemplateOptions([
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId()
            ])
            ->setFrom('general')
            ->addTo($order->getCustomerEmail())
            ->setTemplateVars($transportObject->getData())
            ->getTransport();

        try {
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
}
