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
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Email extends AbstractHelper
{
    /**
     * @const Is Enabled Transaction In Validation.
     */
    const PATH_IS_ENABLED_TRANSACTION_IN_VALIDATION = 'clearsale_configuration/transaction_in_validation/enabled';

    /**
     * @const Path Template Transaction In Validation.
     */
    const PATH_TEMPLATE_TRANSACTION_IN_VALIDATION = 'clearsale_configuration/transaction_in_validation/template';

    /**
     * @const Path Email Transaction In Validation Subject.
     */
    const PATH_TRANSACTION_IN_VALIDATION_SUBJECT = 'clearsale_configuration/transaction_in_validation/subject';

    /**
     * @const Path Email Transaction In Validation Text.
     */
    const PATH_TRANSACTION_IN_VALIDATION_TEXT = 'clearsale_configuration/transaction_in_validation/text';

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
     * @var PaymentHelper
     */
    protected $_paymentHelper;

    /**
     * @var Renderer
     */
    protected $_addressRenderer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     * @param PaymentHelper $paymentHelper
     * @param Renderer $addressRenderer
     * @param Context $context
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        PaymentHelper $paymentHelper,
        Renderer $addressRenderer,
        Context $context
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_logger = $logger;
        $this->_paymentHelper = $paymentHelper;
        $this->_addressRenderer = $addressRenderer;
        parent::__construct($context);
    }

    /**
     * Gets transaction in validation text.
     *
     * @param $storeId
     * @return mixed
     */
    public function getTransactionInValidationTest($storeId)
    {
        return $this->scopeConfig->getValue(self::PATH_TRANSACTION_IN_VALIDATION_TEXT, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param Order $order
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendTransactionInValidationMail(Order $order)
    {
        if ($this->scopeConfig->getValue(self::PATH_IS_ENABLED_TRANSACTION_IN_VALIDATION, ScopeInterface::SCOPE_STORE, $order->getStoreId())) {
            $transport = [
                'order' => $order,
                'billing' => $order->getBillingAddress(),
                'store' => $order->getStore(),
                'subject' => $this->scopeConfig->getValue(self::PATH_TRANSACTION_IN_VALIDATION_SUBJECT, ScopeInterface::SCOPE_STORE, $order->getStoreId()),
                'text' => $this->scopeConfig->getValue(self::PATH_TRANSACTION_IN_VALIDATION_TEXT, ScopeInterface::SCOPE_STORE, $order->getStoreId()),
                'payment_html' => $this->getPaymentHtml($order),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            ];
            $transportObject = new DataObject($transport);
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue(self::PATH_TEMPLATE_TRANSACTION_IN_VALIDATION, ScopeInterface::SCOPE_STORE, $order->getStoreId()))
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

    /**
     * Get payment info block as html.
     *
     * @param Order $order
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getPaymentHtml(Order $order): string
    {
        try {
            return $this->_paymentHelper->getInfoBlockHtml(
                $order->getPayment(),
                $this->_storeManager->getStore()->getId()
            );
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }

    /**
     * Render shipping address into html.
     *
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedShippingAddress(Order $order): ?string
    {
        return $order->getIsVirtual()
            ? null
            : $this->_addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * Render billing address into html.
     *
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedBillingAddress(Order $order): ?string
    {
        return $this->_addressRenderer->format($order->getBillingAddress(), 'html');
    }
}
