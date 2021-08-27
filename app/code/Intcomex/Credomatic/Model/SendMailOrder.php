<?php

namespace Intcomex\Credomatic\Model;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface; 
use Magento\Framework\DataObject; 

class SendMailOrder extends \Magento\Sales\Model\Order\Email\Sender\OrderSender {


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

    }

    public function send(Order $order, $forceSyncMode = false)
    {
        $payment = $order->getPayment();

        if($order->getPayment()->getMethodInstance()->getCode()!='ingenico'){
            if($payment->getLastTransId()==''){
            return false;
            }
        }

        if($order->getPayment()->getMethodInstance()->getCode()=='mercadopago_custom'){
            $paymentData = $payment->getAdditionalInformation();
            if(isset($paymentData['paymentResponse']['status'])){
                if($paymentData['paymentResponse']['status']!='approved'){
                    return false;
                }
            }else{
                return false;
            }
        }
            
        $order->setSendEmail($this->identityContainer->isEnabled());

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            if ($this->checkAndSend($order)) {
                $order->setEmailSent(true);
                $this->orderResource->saveAttribute($order, ['send_email', 'email_sent']);
                return true;
            }
        } else {
            $order->setEmailSent(null);
            $this->orderResource->saveAttribute($order, 'email_sent');
        }

        $this->orderResource->saveAttribute($order, 'send_email');

        return false;
    }

}