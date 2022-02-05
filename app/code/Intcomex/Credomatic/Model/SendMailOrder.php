<?php

namespace Intcomex\Credomatic\Model;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Zend\Log\Logger;

class SendMailOrder extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
    /**
     * @var Logger
     */
    private $_logger;

    public function __construct(
        Template $templateContainer,
        OrderIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderResource $orderResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer, $paymentHelper, $orderResource, $globalConfig, $eventManager);
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/OrderEmail.log');
        $logger = new Logger();
        $logger->addWriter($writer);
        $this->_logger = $logger;
    }

    public function send(Order $order, $forceSyncMode = false)
    {
        /** @var Order $order */
        $this->_logger->debug('InitOrderId: ' . $order->getIncrementId());
        $payment = $order->getPayment();
        $code = $order->getPayment()->getMethodInstance()->getCode();
        $this->_logger->debug('OrderId: ' . $order->getIncrementId() . ' PaymentMethod: ' . $code);

        if ($code === 'mercadopago_custom' || $code === 'mercadopago_basic') {
            $paymentData = $payment->getAdditionalInformation();
            if(isset($paymentData['paymentResponse']['status'])){
                $this->_logger->debug('OrderId: ' . $order->getIncrementId() . ' IssetStatus');
                if($paymentData['paymentResponse']['status']!='approved'){
                    $this->_logger->debug('OrderId: ' . $order->getIncrementId() . ' Status != Approved: ');
                    return false;
                }
            }else{
                $this->_logger->debug('OrderId: ' . $order->getIncrementId() . ' NotIssetStatus');
                return false;
            }
        } else if ($code!='ingenico') {
            $this->_logger->debug('OrderId: ' . $order->getIncrementId() . ' PaymentMethod != Ingenico');
            if($payment->getLastTransId()==''&&$payment->getLastTransId()!=null){
                $this->_logger->debug('OrderId: ' . $order->getIncrementId() . ' Ingenico $payment->getLastTransId(): ' . $payment->getLastTransId());
                return false;
            }
        }

        $order->setSendEmail($this->identityContainer->isEnabled());
        $this->_logger->debug('OrderId: ' . $order->getIncrementId() . ' $order->setSendEmail: ' . $this->identityContainer->isEnabled());

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            $this->_logger->debug('OrderId: ' . $order->getIncrementId() . ' async_sending ' . $this->globalConfig->getValue('sales_email/general/async_sending') . ' forceSyncMode ' . $forceSyncMode);
            if ($this->checkAndSend($order)) {
                $order->setEmailSent(true);
                $this->orderResource->saveAttribute($order, ['send_email', 'email_sent']);
                $this->_logger->debug('OrderId: ' . $order->getIncrementId() . ' $order->getEmailSent: (true) ' . $order->getEmailSent());
                return true;
            } else {
                $this->_logger->debug('OrderId: ' . $order->getIncrementId() . ' $this->checkAndSend($order) == false ');
            }
        } else {
            $order->setEmailSent(null);
            $this->orderResource->saveAttribute($order, 'email_sent');
            $this->_logger->debug('OrderId: ' . $order->getIncrementId() . ' $order->getEmailSent: (null) ' . $order->getEmailSent());
        }

        $this->orderResource->saveAttribute($order, 'send_email');

        return false;
    }
}
