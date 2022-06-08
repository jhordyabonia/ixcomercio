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
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Intcomex\Credomatic\Model\CredomaticFactory $credomaticFactory
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_credomaticFactory = $credomaticFactory;
        $this->_credomaticFactory = $credomaticFactory;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/credomatic_request.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
    }

    public function execute(){ 

        $arrayData = [];

        try {
            
            $resultJson = $this->resultJsonFactory->create();
            $quote_ = json_encode($this->_checkoutSession->getQuote()->getData());
            $quote_items = $this->_checkoutSession->getQuote()->getAllItems();
            $skuItems = [];
            
            foreach($quote_items as $key => $dataItem){
                $skuItems[$dataItem->getSku()] = $dataItem->getData();
            }
            $skuItems = json_encode($skuItems);

            $model =  $this->_credomaticFactory->create();
            $model->addData([
                'quote_id' => $this->_checkoutSession->getQuoteId(),
                'copy_quote_data' => $quote_ ,
                'copy_quote_data_items' => $skuItems,
                'created_at' => strtotime(date('Y-m-d H:i:s'))
            ]);
            $model->save();
            
            $this->logger->info("BinCampaign_CopyQuoteData: " . print_r($quote_ , true));

            $arrayData['response'] = 'success';

        }catch (\Exception $e) {
            $this->logger->info("BinCampaign: " .$e->getMessage());
            $arrayData['response'] = 'fail';
        }

        $resultJson->setData($arrayData);
        return $resultJson;
    }
}