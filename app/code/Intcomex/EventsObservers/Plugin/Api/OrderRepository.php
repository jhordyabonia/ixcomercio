<?php 
namespace Intcomex\EventsObservers\Plugin\Api; 

class OrderRepository {


    /**
     * Order feedback field name
     */
    const BILLING_ADDRESS_IDENTIFICACTION = 'billing_address_identification';
    const TRANSACTION_VALUE_ID = 'transaction_value_id';
    const CUSTOMER_ID = 'trax_general/catalogo_retailer/customer_id';
    
    protected $_scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }


    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject, 
        $entity
    ) {
        $guide_number = array();
        $trackin_url = array();
        $url_pdf_guide = array();
        $extensionAttributes = $entity->getExtensionAttributes();
        
        $order_billing = $entity->getBillingAddress()->getData();
        $order_paymet = $entity->getPayment()->getData();

        $statusHistoryItem = $entity->getStatusHistoryCollection()->getFirstItem();
        $comment = $statusHistoryItem->getComment();
        $explode = explode("\n",$comment);
        if(!empty($comment)){
            $guide_number = (!empty($explode[2]))?$explode[2]:"";
            $trackin_url = (!empty($explode[5]))?$explode[5]:"";
            $url_pdf_guide = (!empty($explode[8]))?$explode[8]:"";
        }

        $customer_id = $this->_scopeConfig->getValue(self::CUSTOMER_ID,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customer_id = (!empty($customer_id))?$customer_id:"";

        if ($extensionAttributes) {
            $extensionAttributes->setBillingAddressIdentification( $order_billing['identification'] );
            $extensionAttributes->setTransactionValueId( $order_paymet['last_trans_id'] );
            $extensionAttributes->setGuideNumber( $guide_number );
            $extensionAttributes->setTrackingUrl( $trackin_url );
            $extensionAttributes->setUrlPdfGuide( $url_pdf_guide );
            $extensionAttributes->setCustomerId( $customer_id );
            $entity->setExtensionAttributes( $extensionAttributes );
        }
        return $entity;
    }

    /***

    * Add "customer_feedback" extension attribute to order data object to make it accessible in Magento API data
    *
    * @param OrderRepositoryInterface $subject
    * @param OrderSearchResultInterface $searchResult
    *
    * @return OrderSearchResultInterface
    */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject, 
        \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult)
    {
        $guide_number = array();
        $trackin_url = array();
        $url_pdf_guide = array();

        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {

            $statusHistoryItem = $order->getStatusHistoryCollection()->getFirstItem();
            $status = $statusHistoryItem->getStatusLabel();
            $comment = $statusHistoryItem->getComment();
            $explode = explode("\n",$comment);
            if(!empty($comment)){
                $guide_number = (!empty($explode[2]))?$explode[2]:"";
                $trackin_url = (!empty($explode[5]))?$explode[5]:"";
                $url_pdf_guide = (!empty($explode[8]))?$explode[8]:"";
            }
            
            $billing_identification = $order->getData(self::BILLING_ADDRESS_IDENTIFICACTION);

            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            

            $order_billing = $order->getBillingAddress()->getData();
            $order_paymet = $order->getPayment()->getData();
            
            $customer_id = $this->_scopeConfig->getValue(self::CUSTOMER_ID,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $customer_id = (!empty($customer_id))?$customer_id:"";

            $extensionAttributes->setBillingAddressIdentification( $order_billing['identification'] );
            $extensionAttributes->setTransactionValueId( $order_paymet['last_trans_id'] );

            $extensionAttributes->setGuideNumber( $guide_number );
            $extensionAttributes->setTrackingUrl( $trackin_url );
            $extensionAttributes->setUrlPdfGuide( $url_pdf_guide );
            $extensionAttributes->setCustomerId( $customer_id );
            
            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}