<?php

namespace Intcomex\Credomatic\Controller\Custom;
use Magento\Store\Model\ScopeInterface;

class GetOrder extends \Magento\Framework\App\Action\Action
{

    /**
    * @var \Magento\Framework\Controller\Result\JsonFactory
    */
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\Order $modelOrder,
        \Magento\Store\Model\StoreManagerInterface  $storeManagerInterface,
        \Intcomex\Credomatic\Model\CredomaticFactory $credomaticFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Intcomex\Credomatic\Helper\DataRule $credoHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_modelOrder = $modelOrder;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->_credomaticFactory = $credomaticFactory;
        $this->_curl = $curl;
        $this->credoHelper = $credoHelper;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        
        try {
            $resultJson = $this->resultJsonFactory->create();
            $arrayData = array();
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_request.log');
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);

            $post = $this->getRequest()->getPostValue();
            $orderId =  $this->_checkoutSession->getLastOrderId();
            $order = $this->_modelOrder->load($orderId);
            $processor_id = $this->_scopeConfig->getValue('payment/credomatic/processor_id'.$post['cuotas'],ScopeInterface::SCOPE_STORE);

            //Get Quote Copy
            $quote = $this->quoteFactory->create()->load($this->_checkoutSession->getQuote()->getId());
            $getQuote = $quote->getCopyQuoteData();
            $this->logger->info('BinCampaign_CopyQuoteData: ' . print_r($getQuote, true));
            $quote = json_decode($getQuote, true);

            if($this->credoHelper->isBinRule($quote['applied_rule_ids'])){
                $order->setAppliedRuleIds($quote['applied_rule_ids']);
                $order->setSubtotal($quote['subtotal']);
                $order->setBaseSubtotal($quote['base_subtotal']);
                $order->setSubtotalWithDiscount($quote['subtotal_with_discount']);
                $order->setBaseSubtotalWithDiscount($quote['base_subtotal_with_discount']);
                $order->setDiscountCouponAmount($quote['discount_coupon_amount']);
                $order->setBaseDiscountCouponAmount($quote['base_discount_coupon_amount']);
                $order->setDiscountAmount($quote['subtotal'] - $quote['subtotal_with_discount']);
                $order->setGrandTotal($quote['grand_total']);
                $order->setBaseGrandTotal($quote['base_grand_total']);

                $payment = $order->getPayment();
                $payment->setBaseAmountAuthorized($quote['base_grand_total']);
                $payment->setBaseAmountOrdered($quote['base_grand_total']);
                $payment->setAmountOrdered($quote['base_grand_total']);
            }
            
            $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            $order->addStatusToHistory($order->getStatus(), 'Change state order to pending payment with processor_id ' . $processor_id);
            $order->save();
            echo "discount_amount" . $order->getDiscountAmount();
            echo "osb: " . $quote['subtotal'];
            echo "sb: " . $order->getSubtotal();
            echo "sb: " . $order->getGrandTotal();
            $this->logger->info( $order->getIncrementId());
            $this->logger->info('status: ' . $order->getState());

            $billingAddress = $order->getBillingAddress();
            
            $key = $this->_scopeConfig->getValue('payment/credomatic/key',ScopeInterface::SCOPE_STORE);
            
            
            $arrayData['type'] = 'sale';
            $arrayData['key_id'] = $this->_scopeConfig->getValue('payment/credomatic/key_id',ScopeInterface::SCOPE_STORE);
            $arrayData['amount'] = number_format($order->getGrandTotal(),2,".","");
            $arrayData['time'] = strtotime(date('Y-m-d H:i:s'));
            $token = md5($order->getIncrementId().'|'.$arrayData['amount'].'|'.$arrayData['time'].'|'.$key);
            $arrayData['hash'] = $token;
            $arrayData['orderid'] = $order->getIncrementId();
            $arrayData['processor_id'] = $processor_id;
            $arrayData['firstname'] = $billingAddress->getFirstname();
            $arrayData['lastname'] = $billingAddress->getLastname();
            $arrayData['email'] = $billingAddress->getEmail();
            $arrayData['phone'] = $billingAddress->getTelephone();
            $arrayData['street1'] = isset($billingAddress->getStreet()[0]) ? $billingAddress->getStreet()[0] : '';
            $arrayData['street2'] = isset($billingAddress->getStreet()[1]) ? $billingAddress->getStreet()[1] : '';
            $arrayData['redirect'] = $this->storeManagerInterface->getStore()->getBaseUrl().'credomatic/custom/paymentresponse?token='.$token.'';
            $arrayData['url_gateway'] = $this->_scopeConfig->getValue('payment/credomatic/url_gateway',ScopeInterface::SCOPE_STORE);

            
            $model =  $this->_credomaticFactory->create();
            $model->addData([
                'order_id' => $order->getIncrementId(),
                'token' => $token,
                'created_at' => $arrayData['time'],
            ]);
            $model->save();


            $this->logger->info('Data send to credomatic');
            $this->logger->info(print_r($arrayData,true));

        } catch (\Exception $e) {
             
            $arrayData = ['error' => 'true', 'message' => $e->getMessage()];
            $this->logger->info("getOrder_exception: " . print_r($arrayData,true));
        }

        $resultJson->setData($arrayData);
        return $resultJson;
        

    }

}