<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Intcomex\CredomaticMasterCard\Model\Order\Email;

use Magento\Sales\Model\Order;

/**
 * Class OrderCommentSender
 */
class Sender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
    /**
     * @param Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_mastercard_payment_name.log');
            $this->new_logger = new \Zend\Log\Logger();
            $this->new_logger->addWriter($writer);
            $this->new_logger->info($order->getPayment()->getMethodInstance()->getCode());

        $transport = [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
        ];
        $transportObject = new DataObject($transport);

        /**
         * Event argument `transport` is @deprecated. Use `transportObject` instead.
         */
        $this->eventManager->dispatch(
            'email_order_set_template_vars_before',
            ['sender' => $this, 'transport' => $transportObject, 'transportObject' => $transportObject]
        );

        $this->templateContainer->setTemplateVars($transportObject->getData());

        parent::prepareTemplate($order);
    }
}

