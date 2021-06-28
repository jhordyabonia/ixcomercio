<?php

namespace Intcomex\MercadopagoRewrites\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendMailOnOrderFailure implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderModel;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var logger
     */

    /**
     * @var \Intcomex\MercadopagoRewrites\Helper\Email
     */
    protected $email;


    protected $logger;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderModel
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Intcomex\MercadopagoRewrites\Helper\Email $email
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Intcomex\MercadopagoRewrites\Helper\Email $email
    )
    {
        $this->orderModel = $orderModel;
        $this->orderSender = $orderSender;
        $this->checkoutSession = $checkoutSession;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/senMailFailure.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $this->help_email = $email;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->info("Se ejecuta el observador 'Send mail Failure'");

        $orderids = $observer->getEvent()->getOrderId();
        $order = $observer->getOrder($orderids);
        
        $this->logger->info("Order is: ".$order->getId());
        $this->logger->info("Order status: ".$order->getState());

        

        $payment = $order->getPayment();

        $payment_additional = $payment->getAdditionalInformation('paymentResponse');

        $state_payment = $payment_additional['status'];

        $this->logger->info("Order payment status: ".$state_payment);
        
        if ( $state_payment == 'rejected'){
            $this->logger->info("Order is: ".$order->getId());

            $email = $order->getCustomerEmail();
            $name = $order->getCustomerFirstname()." ". $order->getCustomerLastname();
            
            $method = $payment->getMethodInstance();
            $methodTitle = $method->getTitle();

            $this->logger->info("Order name is: ".$name);
            $this->logger->info("Order email is: ".$email);
            $this->logger->info("Order methodTitle is: ".$methodTitle);          


            $this->help_email->notify($name, $email, $methodTitle);
        } 
    }
}