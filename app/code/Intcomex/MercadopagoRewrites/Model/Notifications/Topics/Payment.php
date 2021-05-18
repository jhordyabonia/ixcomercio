<?php

namespace MercadoPago\Core\Model\Notifications\Topics;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Status\Collection as StatusFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\ScopeInterface;
use MercadoPago\Core\Helper\ConfigData;
use MercadoPago\Core\Helper\Data as mpHelper;
use MercadoPago\Core\Helper\Message\MessageInterface;
use MercadoPago\Core\Helper\Response;
use MercadoPago\Core\Lib\RestClient;
use MercadoPago\Core\Model\Core;

class Payment 
{
    const LOG_NAME    = 'notification_payment';
    const TYPES_TOPIC = [
        'payment',
        'merchant_order',
    ];

    protected $_mpHelper;

    protected $_scopeConfig;

    protected $_coreModel;


    /**
     * Payment constructor.
     *
     * @param mpHelper             $mpHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param Core                 $coreModel
     * @param OrderFactory         $orderFactory
     * @param CreditmemoFactory    $creditmemoFactory
     * @param MessageInterface     $messageInterface
     * @param StatusFactory        $statusFactory
     * @param OrderSender          $orderSender
     * @param OrderCommentSender   $orderCommentSender
     * @param TransactionFactory   $transactionFactory
     * @param InvoiceSender        $invoiceSender
     * @param InvoiceService       $invoiceService
     */
    public function __construct(
        mpHelper $mpHelper,
        ScopeConfigInterface $scopeConfig,
        Core $coreModel,
        OrderFactory $orderFactory,
        CreditmemoFactory $creditmemoFactory,
        MessageInterface $messageInterface,
        StatusFactory $statusFactory,
        OrderSender $orderSender,
        OrderCommentSender $orderCommentSender,
        TransactionFactory $transactionFactory,
        InvoiceSender $invoiceSender,
        InvoiceService $invoiceService
    ) {
        $this->_mpHelper    = $mpHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreModel   = $coreModel;

        parent::__construct($scopeConfig, $mpHelper, $orderFactory, $creditmemoFactory, $messageInterface, $statusFactory, $orderSender, $orderCommentSender, $transactionFactory, $invoiceSender, $invoiceService);

    }//end __construct()



    /**
     * @param  $payment
     * @return array
     * @throws Exception
     */
    public function updateStatusOrderByPayment($payment)
    {
        //$order = parent::getOrderByIncrementId($payment['external_reference']);
        $objectManager=\Magento\Framework\App\ObjectManager::getInstance();

        $helper = $objectManager->get('Intcomex\MercadopagoRewrites\Helper\Api');

        $order = $helper->getOrdenByIncrementId('17000000159');

        if (!$order->getId()) {
            $message = 'Mercado Pago - The order was not found in Magento. You will not be able to follow the process without this information.';
            return [
                'httpStatus' => Response::HTTP_NOT_FOUND,
                'message'    => $message,
                'data'       => $payment['external_reference'],
            ];
        }

        $message              = parent::getMessage($payment);
        $statusAlreadyUpdated = $this->checkStatusAlreadyUpdated($payment, $order);
        $newOrderStatus       = parent::getConfigStatus($payment, $order->canCreditmemo());
        $currentOrderStatus   = $order->getState();

        if ($order->getGrandTotal() > $payment['transaction_details']['total_paid_amount']) {
            $newOrderStatus = 'fraud';
            $message       .= __('<br/> Order total: %s', $order->getGrandTotal());
            $message       .= __('<br/> Paid: %s', $payment['transaction_details']['total_paid_amount']);
        }

        if ($statusAlreadyUpdated) {
            $orderPayment = $order->getPayment();
            $orderPayment->setAdditionalInformation('paymentResponse', $payment);
            $order->save();

            $messageHttp = 'Mercado Pago - Status has already been updated.';
            return [
                'httpStatus' => Response::HTTP_OK,
                'message'    => $messageHttp,
                'data'       => [
                    'message'              => $message,
                    'order_id'             => $order->getIncrementId(),
                    'current_order_status' => $currentOrderStatus,
                    'new_order_status'     => $newOrderStatus,
                ],
            ];
        }

        $order = self::setStatusAndComment($order, $newOrderStatus, $message);

        $this->sendEmailCreateOrUpdate($order, $message);
        $responseInvoice = false;
        if ($payment['status'] == 'approved') {
            $responseInvoice = $this->createInvoice($order, $message);
            $this->addCardInCustomer($payment);
        }

        $this->updateAdditionalInformation($order, $payment);

        $order->save();

        $messageHttp = 'Mercado Pago - Status successfully updated.';
        return [
            'httpStatus' => Response::HTTP_OK,
            'message'    => $messageHttp,
            'data'       => [
                'message'          => $message,
                'order_id'         => $order->getIncrementId(),
                'new_order_status' => $newOrderStatus,
                'old_order_status' => $currentOrderStatus,
                'created_invoice'  => $responseInvoice,
            ],
        ];

    }//end updateStatusOrderByPayment()

}