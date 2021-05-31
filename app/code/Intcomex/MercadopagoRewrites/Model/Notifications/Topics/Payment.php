<?php

namespace Intcomex\MercadopagoRewrites\Model\Notifications\Topics;

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
use Intcomex\MercadopagoRewrites\Helper\Api;
use \Psr\Log\LoggerInterface;

class Payment extends \MercadoPago\Core\Model\Notifications\Topics\Payment
{
    const LOG_NAME    = 'notification_payment';
    const TYPES_TOPIC = [
        'payment',
        'merchant_order',
    ];

    protected $_mpHelper;

    protected $_scopeConfig;

    protected $_coreModel;

    protected $logger;



    /**
     * @param  $payment
     * @return array
     * @throws Exception
     */
    public function updateStatusOrderByPayment($payment)
    {
        //Ajuste de llamado orden details
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        $helper = $objectManager->create('Intcomex\MercadopagoRewrites\Helper\Api');
        $order =  $helper->getOrdenByIncrementId($payment['external_reference'],0);
        
        if (!$order->getId()) {

            $oder_canceled = $helper->setOrdenStatusCanceled($payment['external_reference']);            

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

        if ($payment['status'] == 'approved') {
            $helper->getRegysterPayment($order);
        }

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


    /**
     * @param  $paymentResponse
     * @param  $order
     * @return boolean
     */
    public function checkStatusAlreadyUpdated($paymentResponse, $order)
    {
        $orderUpdated   = false;
        $statusToUpdate = parent::getConfigStatus($paymentResponse, false);
        $commentsObject = $order->getStatusHistoryCollection(true);
        foreach ($commentsObject as $commentObj) {
            if ($commentObj->getStatus() == $statusToUpdate) {
                $orderUpdated = true;
            }
        }

        return $orderUpdated;

    }//end checkStatusAlreadyUpdated()


    /**
     * @param $order
     * @param $message
     */
    public function sendEmailCreateOrUpdate($order, $message)
    {
        $emailOrderCreate = $this->_scopeConfig->getValue(ConfigData::PATH_ADVANCED_EMAIL_CREATE, ScopeInterface::SCOPE_STORE);
        $emailAlreadySent = false;
        if ($emailOrderCreate) {
            if (!$order->getEmailSent()) {
                $this->_orderSender->send($order, true);
                $emailAlreadySent = true;
            }
        }

        if ($emailAlreadySent === false) {
            $statusEmail     = $this->_scopeConfig->getValue(ConfigData::PATH_ADVANCED_EMAIL_UPDATE, ScopeInterface::SCOPE_STORE);
            $statusEmailList = explode(',', $statusEmail);
            if (in_array($order->getStatus(), $statusEmailList)) {
                $this->_orderCommentSender->send($order, $notify = '1', str_replace('<br/>', '', $message));
            }
        }

    }//end sendEmailCreateOrUpdate()


    /**
     * @param  $order
     * @param  $message
     * @return boolean
     * @throws Exception
     */
    public function createInvoice($order, $message)
    {
        if (!$order->hasInvoices()) {
            $invoice = $order->prepareInvoice();
            $invoice->register();
            $invoice->pay();
            $invoice->addComment(str_replace('<br/>', '', $message), false, true);

            $transaction = $this->_transactionFactory->create();
            $transaction->addObject($invoice);
            $transaction->addObject($invoice->getOrder());
            $transaction->save();
            $this->_invoiceSender->send($invoice, true, $message);
            return true;
        }

        return false;

    }//end createInvoice()


    /**
     * @param  $paymentResponse
     * @return array
     */
    public function addCardInCustomer($paymentResponse)
    {
        if (isset($paymentResponse['metadata'])
            && isset($paymentResponse['metadata']['customer_id'])
            && isset($paymentResponse['metadata']['token'])
            && isset($paymentResponse['payment_method_id'])
            && isset($paymentResponse['issuer_id'])
        ) {
            $customer_id       = $paymentResponse['metadata']['customer_id'];
            $token             = $paymentResponse['metadata']['token'];
            $payment_method_id = $paymentResponse['payment_method_id'];
            $issuer_id         = (int) $paymentResponse['issuer_id'];

            $accessToken = $this->_scopeConfig->getValue(ConfigData::PATH_ACCESS_TOKEN, ScopeInterface::SCOPE_STORE);
            $request     = [
                'token'             => $token,
                'issuer_id'         => $issuer_id,
                'payment_method_id' => $payment_method_id,
            ];
            $card        = RestClient::post('/v1/customers/'.$customer_id.'/cards', $request, null, ['Authorization: Bearer '.$accessToken]);
            return $card;
        }

    }//end addCardInCustomer()


    /**
     * @param  $id
     * @param  null $type
     * @return array
     */
    public function getPaymentData($id, $type=null)
    {
        try {
            $response = $this->_coreModel->getPayment($id);
            $this->_mpHelper->log('Response API MP Get Payment', self::LOG_NAME, $response);

            if (!$this->isValidResponse($response)) {
                throw new Exception(__('MP API Invalid Response'), 400);
            }

            $payments   = [];
            $payments[] = $response['response'];

            return [
                'merchantOrder' => null,
                'payments'      => $payments,
                'shipmentData'  => null,
            ];
        } catch (\Exception $e) {
            $this->_mpHelper->log(__('ERROR - Notifications Payment getPaymentData'), self::LOG_NAME, $e->getMessage());
        }

    }//end getPaymentData()


    /**
     * @param Order $order
     * @param array $payment
     */
    protected function updateAdditionalInformation($order, $payment)
    {
        $orderPayment          = $order->getPayment();
        $additionalInformation = $orderPayment->getAdditionalInformation('paymentResponse');

        if (!empty($payment['card'])) {
            $card = $payment['card'];

            if (isset($card['last_four_digits'])) {
                $orderPayment->setCcLast4($card['last_four_digits']);
            }

            if (isset($card['expiration_month'])) {
                $orderPayment->setCcExpMonth($card['expiration_month']);
            }

            if (isset($card['expiration_year'])) {
                $orderPayment->setCcExpYear($card['expiration_year']);
            }

            $orderPayment->setCcType($payment['payment_method_id']);
            $orderPayment->setCcTransId($payment['id']);
        }

        $additionalInformation['status']        = $payment['status'];
        $additionalInformation['status_detail'] = $payment['status_detail'];

        $order->getPayment()->setAdditionalInformation('paymentResponse', $additionalInformation);

    }//end updateAdditionalInformation()


}//end class
