<?php


namespace Intcomex\CustomLog\Observer;

use Magento\Framework\Event\ObserverInterface;


class SalesOrderSaveAfter implements ObserverInterface
{
    /**
     * @var \Zend\Log\Logger
     */
    protected $logger;

    /**
     * Http Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;


    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->request = $request;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sales_order_save_after.log');

        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $this->authSession = $authSession;
    }

    /**
     *
     *  @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $this->logger->info("Order Info", ["data" => $order->getData()]);
    }
}