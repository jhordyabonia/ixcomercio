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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/SendMailOrder.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $payment = $order->getPayment();

        $code = $order->getPayment()->getMethodInstance()->getCode();
        if($code!='ingenico'&&$code!='mercadopago_custom'&&$code!='mercadopago_basic'){

            if($code=='pagalo'||$code=='pagalovisa'||$code=='pagalomastercard'){
                if (empty($payment->getLastTransId())){
                    return false; 
                }else{
                    $this->logger->info('se envia corrreo para la orden');
                    $this->logger->info('Orden: '.$order->getId());
                    $this->logger->info('Pasarela: '.$code);
                    $this->logger->info('getLastTransId: '.$payment->getLastTransId());
                }
            }else{
                $getIsPaid = $this->getIsPaid($order->getId());
                $isPaid = (isset($getIsPaid))?$getIsPaid:-1;
                $this->logger->info('getIsPaid '.$isPaid);

                if($isPaid==0||$isPaid==-1){
                    $this->logger->info('return false por validacion');
                    return false; 
                }else{
                    $this->logger->info('se envia corrreo para la orden');
                    $this->logger->info('Orden: '.$order->getId());
                    $this->logger->info('Pasarela: '.$code);
                }

            }
            
        }

        if($code=='mercadopago_custom'||$code=='mercadopago_basic'){
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

    public function getIsPaid($orderid){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('sales_order'); //gives table name with prefix
			//Select Data from table
			$sql = "Select is_paid FROM " . $tableName." WHERE entity_id=".$orderid;
			return (int)$connection->fetchOne($sql); // gives associated array, table fields as key in array.
    }

}