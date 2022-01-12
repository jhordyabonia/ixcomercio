<?php

namespace Intcomex\Clearsale\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\CreditmemoService;
use Psr\Log\LoggerInterface;

class RefundOrder
{
    /**
     * @var CreditmemoFactory
     */
    protected $creditMemoFactory;

    /**
     * @var CreditmemoService
     */
    protected $creditMemoService;

    protected $logger;

    /**
     * @var Invoice
     */
    protected $invoice;

    /**
     * @param CreditmemoFactory $creditMemoFactory
     * @param CreditmemoService $creditMemoService
     * @param LoggerInterface $logger
     * @param Invoice $invoice
     */
    public function __construct(
        CreditmemoFactory $creditMemoFactory,
        CreditmemoService $creditMemoService,
        LoggerInterface $logger,
        Invoice $invoice
    ) {
        $this->creditMemoFactory = $creditMemoFactory;
        $this->creditMemoService = $creditMemoService;
        $this->logger = $logger;
        $this->invoice = $invoice;
    }

    /**
     * Refund Order.
     *
     * @param OrderInterface $order
     * @throws LocalizedException
     */
    public function refund(OrderInterface $order): void
    {
        $invoices = $order->getInvoiceCollection();

        if (count($invoices) === 0) {
            $this->logger->error(__('No Invoices found for Refund. Order: %1', $order->getIncrementId()));
            return;
        }

        foreach ($invoices as $invoice) {
            $invoice = $this->invoice->loadByIncrementId($invoice->getIncrementId());
            $creditMemo = $this->creditMemoFactory->createByOrder($order);

            // Don't set invoice if you want to do offline refund
            $creditMemo->setInvoice($invoice);
            $creditMemo->setCustomerNote(__('Your Order %1 has been Refunded back in your account', $order->getIncrementId()));
            $creditMemo->setCustomerNoteNotify(true);
            $creditMemo->addComment(__('Order has been Refunded'));
            $order->addCommentToStatusHistory(__('Order has been Refunded Successfully'));
            $this->creditMemoService->refund($creditMemo);
            $this->logger->error(__('Order has been Refunded Successfully. Order: %1', $order->getIncrementId()));
        }
    }
}
