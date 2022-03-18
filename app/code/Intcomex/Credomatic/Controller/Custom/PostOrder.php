<?php
namespace Intcomex\Credomatic\Controller\Custom;
use Magento\Store\Model\ScopeInterface;

class PostOrder extends \Magento\Framework\App\Action\Action
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
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Order $modelOrder,
        \Magento\Store\Model\StoreManagerInterface  $storeManagerInterface,
        \Intcomex\Credomatic\Model\CredomaticFactory $credomaticFactory
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->encryptor = $encryptor;
        $this->modelOrder = $modelOrder;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->_credomaticFactory = $credomaticFactory;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){       
        try {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_status_after_postorder.log');
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);
            $post  = $this->getRequest()->getParams();
            if(!empty($post)){

                $order = $this->modelOrder->loadByIncrementId($post['orderid']);
                $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, true);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $order->addStatusToHistory($order->getStatus(), 'Order pending payment successfully with reference');
                $order->save();
                $this->logger->info('-----');
                $this->logger->info('status');
                $this->logger->info($post['orderid']);
                $this->logger->info($order->getState());
                $this->logger->info('-----');

                $token = substr(md5(uniqid(rand())), 0, 49);

                $time = strtotime(date('Y-m-d H:i:s'));
                $hash = md5($post['orderid'].'|'.$post['amount'].'|'.$time.'|'.$this->_scopeConfig->getValue('payment/credomatic/key',ScopeInterface::SCOPE_STORE));
                $form = '<form action="https://credomatic.compassmerchantsolutions.com/api/transact.php" method="POST"   id="formCredomatic">';
                $form .= '<input type="hidden" readonly id="credomatic_type" name="type" value="sale"  >';
                $form .= '<input type="hidden" readonly id="credomatic_key_id" name="key_id" value="'.$post['key_id'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_hash" name="hash" value="'.$hash.'" >';
                $form .= '<input type="hidden" readonly id="credomatic_time" name="time" value="'.$time.'" >';
                $form .= '<input type="hidden" readonly id="credomatic_amount" name="amount" value="'.$post['amount'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_orderid" name="orderid" value="'.$post['orderid'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_processor_id" name="processor_id" value="'.$post['processor_id'].'"  >';
                $form .= '<input type="hidden" readonly id="credomatic_firstname" name="firstname" value="'.$post['firstname'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_lastname" name="lastname" value="'.$post['lastname'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_email" name="email" value="'.$post['email'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_phone" name="phone" value="'.$post['phone'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_street1" name="street1" value="'.$post['address1'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_street2" name="street2" value="'.$post['address2'].'" >';
                $form .= '<input type="hidden" readonly id="credomatic_cvv" name="cvv" value="'.$this->decrypt($post['data1']).'"  >';
                $form .= '<input type="hidden" readonly id="credomatic_ccnumber" name="ccnumber" value="'.$this->decrypt($post['data2']).'" >';
                $form .= '<input type="hidden" readonly id="credomatic_ccexp" name="ccexp" value="'.$this->decrypt($post['data3']).'"  >';
                $form .= '<input type="hidden" readonly id="credomatic_redirect" name="redirect" value="'.$this->storeManagerInterface->getStore()->getBaseUrl().'credomatic/custom/registerresponse?token='.$token.'"  >';
                $form .= '</form>';
                $form .= '<script>';
                $form .= 'setTimeout(function(){ document.getElementById("formCredomatic").submit(); }, 2000)';
                $form .= '</script>';
                echo $form;

                $model =  $this->_credomaticFactory->create();
                $model->addData([
                        'order_id' => $post['orderid'],
                        'token' => $token,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                $model->save();


            }
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }

    }

    public function decrypt($value){
    return $this->encryptor->decrypt($value);
    }

}