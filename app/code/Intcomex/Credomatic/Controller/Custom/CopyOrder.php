<?php

namespace Intcomex\Credomatic\Controller\Custom;
use Magento\Store\Model\ScopeInterface;

class CopyOrder extends \Magento\Framework\App\Action\Action
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
        \Magento\Quote\Model\QuoteFactory $QuoteFactory
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_modelOrder = $modelOrder;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->_credomaticFactory = $credomaticFactory;
        $this->_curl = $curl;
        $this->QuoteFactory = $QuoteFactory;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_request.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
    }

    public function execute(){ 

        $arrayData = [];

        try {

            $resultJson = $this->resultJsonFactory->create();
            $quote_ = json_encode($this->_checkoutSession->getQuote()->getData());
            $quote = $this->QuoteFactory->create()->load($this->_checkoutSession->getQuote()->getId());
            $quote->setCopyQuoteData($quote_);
            $quote->save();
            
            $this->logger->info("BinCampaign_CopyQuoteData: " . print_r($quote->getData(), true));

            $arrayData['response'] = 'success';

        }catch (\Exception $e) {
            $this->logger->info("BinCampaign: " .$e->getMessage());
            $arrayData['response'] = 'fail';
        }

        $resultJson->setData($arrayData);
        return $resultJson;
    }
}