<?php

namespace Intcomex\Clearsale\Observer;

use Intcomex\Clearsale\Helper\Email;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;

class EmailTransactionValidation implements ObserverInterface
{
    /**
     * @var Email
     */
    protected $_helperEmail;

    /**
     * @param Email $helperEmail
     */
    public function __construct(
        Email $helperEmail
    ) {
        $this->_helperEmail = $helperEmail;
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
        if ($order && $order->getPayment()->getMethod() === 'adyen_cc' && $order->getStatus() !== 'approved_clearsale') {
            $this->_helperEmail->sendTransactionInValidationMail($order);
        }
    }
}
