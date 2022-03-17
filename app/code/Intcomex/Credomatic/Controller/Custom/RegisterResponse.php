<?php

namespace Intcomex\Credomatic\Controller\Custom;

class RegisterResponse extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Intcomex\Credomatic\Model\CredomaticFactory $credomaticFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    ) {
        parent::__construct($context);
        $this->json = $json;
        $this->_credomaticFactory = $credomaticFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->orderManagement = $orderManagement;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->transaction = $transaction;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Execute view action.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Validator\Exception
     */
    public function execute()
    {
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
            }
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }
    }
}
