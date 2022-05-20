<?php

namespace Intcomex\Clearsale\Observer;

use Intcomex\Clearsale\Helper\Email;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class EmailTransactionValidation implements ObserverInterface
{
    /**
     * @var Email
     */
    protected $_helperEmail;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param Email $helperEmail
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Email $helperEmail,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_helperEmail = $helperEmail;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getData('order');
        if ($order && $order->getEntityId()) {
            $isActive = $this->_scopeConfig->getValue('clearsale_configuration/cs_config/active', ScopeInterface::SCOPE_STORE, $order->getStoreId());
            if ($isActive) {
                if ($order->getPayment() && $order->getPayment()->getMethod() === 'adyen_cc' && $order->getStatus() !== 'approved_clearsale') {
                    $this->_helperEmail->sendTransactionInValidationMail($order);
                }
            }
        }
    }
}
