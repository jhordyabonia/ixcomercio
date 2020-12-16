<?php 
namespace Intcomex\EventsObservers\Plugin\Api; 

class OrderRepository {


    /**
     * Order feedback field name
     */
    const BILLING_ADDRESS_IDENTIFICACTION = 'billing_address_identification';
    const TRANSACTION_VALUE_ID = 'transaction_value_id';
   
    public function __construct()
    {
        
    }


    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject, 
        $entity
    ) {

        $extensionAttributes = $entity->getExtensionAttributes();
        
        $order_billing = $entity->getBillingAddress()->getData();
        $order_paymet = $entity->getPayment()->getData();        

        if ($extensionAttributes) {
            $extensionAttributes->setBillingAddressIdentification( $order_billing['identification'] );
            $extensionAttributes->setTransactionValueId( $order_paymet['last_trans_id'] );
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
            $order_paymet = $order->getPayment()->getData();
            

            $extensionAttributes->setBillingAddressIdentification( $order_billing['identification'] );
            $extensionAttributes->setTransactionValueId( $order_paymet['last_trans_id'] );
            
            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }


}