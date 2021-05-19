<?php
namespace Intcomex\MercadopagoRewrites\Model\Notifications\Topics;

use MercadoPago\Core\Model\Notifications\Topics\Payment;

class PaymentPlugin  
{  
    /**
     * @param  $payment
     * @return array
     * @throws Exception
     */
    public function beforeUpdateStatusOrderByPayment(Payment $payment)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
       $helper = $objectManager->create('Intcomex\MercadopagoRewrites\Helper\Api');
        $order = $helper->getOrdenByIncrementId($payment['external_reference']);
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/mpresp.log');
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);
            $this->logger->info(print_r($payment['external_reference'],true));
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