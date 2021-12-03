<?php

namespace Intcomex\Credomatic\Controller\Custom;

use Intcomex\Credomatic\Model\CredomaticFactory;

class RegisterResponse extends \Magento\Framework\App\Action\Action
{


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Intcomex\Credomatic\Model\CredomaticFactory $credomaticFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context);
        $this->json = $json;
        $this->_credomaticFactory = $credomaticFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->orderManagement = $orderManagement;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {
            $get = $this->getRequest()->getParams();

            if(!empty($get)){
                $model =  $this->_credomaticFactory->create();  
                $data = $model->getCollection()->addFieldToFilter('order_id', array('eq' => $get['orderid']));
                
                if(empty($data->getData())){
                    $model->addData([
                        'order_id' => $get['orderid'],
                        'response' => $this->json->serialize($get),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                        ]);
    
                     $model->save();
                }

                $order = $this->_orderFactory->create()->loadByIncrementId($body['orderid']);

                if($get['response_code']==100){
                    
                    $order->setState("processing")->setStatus("processing");
                    $payment = $order->getPayment();
                    $payment->setLastTransId($body['authcode']);
                    $payment->save();
                    $order->save();
                    $this->_checkoutSession->setLastQuoteId($order->getId());
                    $this->_checkoutSession->setLastSuccessQuoteId($order->getId());
                    $this->_checkoutSession->setLastOrderId($order->getId()); // Not incrementId!!
                    $this->_checkoutSession->setLastRealOrderId($body['orderid']);

                    $this->orderSender->send($order, true);
    
                    if ($order->canInvoice()) {
                        $invoice = $this->invoiceService->prepareInvoice($order);
                        $invoice->register();
                        $invoice->save();
                        $transactionSave = $this->transaction->addObject(
                            $invoice
                        )->addObject(
                            $invoice->getOrder()
                        );
                        $transactionSave->save();
                        $this->invoiceSender->send($invoice);
                        //Send Invoice mail to customer
                        $order->addStatusHistoryComment(__('Notified customer about invoice creation #%1.', $invoice->getId()))->setIsCustomerNotified(true)->save();
                    }
                }else if($get['response_code']==300||$body['response_code']==200){
                        $order->setState("canceled")->setStatus("canceled");
                        $this->orderManagement->cancel($order->getId());
                        $order->addStatusHistoryComment('Se cancela la order con el sigueinte error: '.((isset($body['responsetext']))?$body['responsetext']:''));
                        $order->save();
                }



            }
    
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }
        
    }


}