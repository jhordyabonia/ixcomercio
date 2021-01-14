<?php 
namespace Intcomex\EventsObservers\Plugin\Api; 

class OrderRepository {


    /**
     * Order feedback field name
     */
    const BILLING_ADDRESS_IDENTIFICACTION = 'billing_address_identification';
    const TRANSACTION_VALUE_ID = 'transaction_value_id';
    const MIENVIO_QUOTE_ID = 'mienvio_quote_id';
    const SHIPPING_ADDRESS_ZONE = 'shipping_address_zone';
   
    public function __construct()
    {
        
    }


    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject, 
        $entity
    ) {

        $extensionAttributes = $entity->getExtensionAttributes();
        
        $order_billing = $entity->getBillingAddress()->getData();
        $order_shipping = $entity->getShippingAddress()->getData();
        $order_paymet = $entity->getPayment()->getData();  
        
        $mienvioQuoteId = $entity->getMienvioQuoteId();

        if ($extensionAttributes) {
            $extensionAttributes->setBillingAddressIdentification( $order_billing['identification'] );
            $extensionAttributes->setTransactionValueId( $order_paymet['last_trans_id'] );
            $extensionAttributes->setMienvioQuoteId( $mienvioQuoteId );
            $extensionAttributes->setShippingAddressZone( $order_shipping['zone_id'] );
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

        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {

            $billing_identification = $order->getData(self::BILLING_ADDRESS_IDENTIFICACTION);

            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            

            $order_billing = $order->getBillingAddress()->getData();
            $order_shipping = $order->getShippingAddress()->getData();
            $order_paymet = $order->getPayment()->getData();
            $mienvioQuoteId = $order->getMienvioQuoteId();            

            $extensionAttributes->setBillingAddressIdentification( $order_billing['identification'] );
            $extensionAttributes->setTransactionValueId( $order_paymet['last_trans_id'] );
            $extensionAttributes->setMienvioQuoteId( $mienvioQuoteId );
            $extensionAttributes->setShippingAddressZone( $order_shipping['zone_id'] );
            
            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }


}