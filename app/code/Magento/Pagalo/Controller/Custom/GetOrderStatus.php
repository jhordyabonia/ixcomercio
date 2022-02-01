<?php

namespace Magento\Pagalo\Controller\Custom;

use Magento\Store\Model\ScopeInterface;


class GetOrderStatus extends \Magento\Framework\App\Action\Action
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
            $orderId =  $this->_checkoutSession->getLastOrderId();
            $order = $this->_orderInterfaceFactory->create()->load($orderId);
            $alert = '';
            if($order->getStatus()=='canceled'){
                $alert =  '<div role="alert"  class="messages"><div  class="message-error error message" data-ui-id="message-error"> <div >Transacción rechazada</div> </div></div>';
            }
            $respProcess = ['status' => 'success', 'message' => array(
                'alert' => $alert
            )];

        } catch (\Exception $e) {
            $respProcess = ['status' => 'error', 'message' => $e->getMessage()];
        }
        $resultJson->setData($respProcess);
        return $resultJson;
        
    }

}