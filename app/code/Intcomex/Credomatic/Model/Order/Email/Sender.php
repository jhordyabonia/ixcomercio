<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Intcomex\Credomatic\Model\Order\Email;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Address\Renderer;

/**
 * Class Sender
 * @api
 *
 * @since 100.0.2
 */
abstract class Sender
{
    /**
     * @var \Magento\Sales\Model\Order\Email\SenderBuilderFactory
     */
    protected $senderBuilderFactory;

    /**
     * @var Template
     */
    protected $templateContainer;

    /**
     * @var IdentityInterface
     */
    protected $identityContainer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer
    ) {
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
        $this->senderBuilderFactory = $senderBuilderFactory;
        $this->logger = $logger;
        $this->addressRenderer = $addressRenderer;
    }

    /**
     * Send order email if it is enabled in configuration.
     *
     * @param Order $order
     * @return bool
     */
    protected function checkAndSend(Order $order)
    {
        $this->identityContainer->setStore($order->getStore());
        if (!$this->identityContainer->isEnabled()) {
            return false;
        }
        $this->prepareTemplate($order);

        /** @var SenderBuilder $sender */
        $sender = $this->getSender();

        try {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_payment_name.log');
            $this->new_logger = new \Zend\Log\Logger();
            $this->new_logger->addWriter($writer);
            $this->new_logger->info($order->getPayment()->getMethodInstance()->getCode());

            if($order->getPayment()->getMethodInstance()->getCode() == "yourpaymentmethodcode"){
                $sender->send();
            }else{
                $sender->send();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
        try {
            $sender->sendCopyTo();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return true;
    }
}
