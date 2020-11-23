<?php

namespace Intcomex\Credomatic\Controller\Custom;

class GetOrder extends \Magento\Framework\App\Action\Action
{


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 

        try {

            $post = $_POST; 
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
            $order = $objectManager->get('Magento\Sales\Model\Order')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('entity_id',array('eq'=>$this->_checkoutSession->getLastOrderId()));
            $arrayData = array();
            $time = strtotime(date('Y-m-d H:i:s'));
            
            $procesor_id = $this->_scopeConfig->getValue('payment/credomatic/processor_id'.$post['cuotas']);
            
            $total = number_format($order->getData()[0]['grand_total'],2,".","");
            $hash = md5($order->getData()[0]['entity_id'].'|'.$total.'|'.$time.'|'.$this->_scopeConfig->getValue('payment/credomatic/key'));
            $arrayData['key_id'] = $this->_scopeConfig->getValue('payment/credomatic/key_id');
            $arrayData['hash'] = $hash;
            $arrayData['time'] = $time;
            $arrayData['processor_id'] = $procesor_id;
            $arrayData['amount'] = $total;
            $arrayData['orderid'] = $order->getData()[0]['entity_id'];
            $arrayData['gateway'] = $this->_scopeConfig->getValue('payment/credomatic/url_gateway');
            $arrayData['ccexp'] = str_pad($post['month'], 2, '0', STR_PAD_LEFT).substr($post['year'], 2, 4);
            echo json_encode($arrayData);
            $arrayData=array();

        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }

    }

}