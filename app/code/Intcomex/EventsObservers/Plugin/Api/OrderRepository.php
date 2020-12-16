<?php 
namespace Intcomex\EventsObservers\Plugin\Api; 

class OrderRepository {

    public function __construct() {
        
    }

    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject, 
        $entity
    ) {

        $extensionAttributes = $entity->getExtensionAttributes();
        
        $order_billing = $entity->getBillingAddress()->getData();
        $order_paymet = $entity->getPayment()->getAdditionalInformation();

        if ($extensionAttributes) {
            $extensionAttributes->setBillingAddressIdentification( $order_billing['identification'] );
            $extensionAttributes->setTransactionValueId( $order_paymet['ingenico_payment_id'] );
            $entity->setExtensionAttributes( $extensionAttributes );
        }
        return $entity;
    }
}