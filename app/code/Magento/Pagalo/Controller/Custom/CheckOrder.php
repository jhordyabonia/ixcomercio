<?php

namespace Magento\Pagalo\Controller\Custom;

use Magento\Store\Model\ScopeInterface;


class CheckOrder extends \Magento\Framework\App\Action\Action
{

    protected $resultRedirect;
    protected $_messageManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\ResultFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\Order $modelOrder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->resultRedirect = $context->getResultFactory();
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->modelOrder = $modelOrder;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderManagement = $orderManagement;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {
            $resultJson = $this->resultJsonFactory->create();
            $post  = $this->getRequest()->getPostValue();
            $respProcess = array();
            $customError =  $this->_scopeConfig->getValue('payment/pagalo/PGCustomErrorMsg',ScopeInterface::SCOPE_STORE);
            $orderId =  $this->_checkoutSession->getLastOrderId();
            $order = $this->modelOrder->load($orderId);
            $payment = $order->getPayment();
            $redirect = 'checkout/onepage/success';
            if(empty($payment->getLastTransId())){
                $this->_messageManager->addError('Transacción Rechazada');  
                $this->_checkoutSession->restoreQuote();
                $this->orderManagement->cancel($order->getId());
                $order->addStatusHistoryComment('Se cancela la orden por transaccion rechazada');
                $order->setState("canceled")->setStatus("canceled");
                $order->cancel();
                $order->save();
                $redirect = 'checkout/cart';
            }
            $respProcess = ['status' => 'success', 'message' => array(
                'order_id' => $order->getIncrementId(),
                'status' => $order->getStatus(),
                'redirect' => $redirect,
                'error' => 'Transacción Rechazada'
            )];

        } catch (\Exception $e) {
            $respProcess = ['status' => 'error', 'message' => $e->getMessage()];
        }
        $resultJson->setData($respProcess);
        return $resultJson;
        
    }

}