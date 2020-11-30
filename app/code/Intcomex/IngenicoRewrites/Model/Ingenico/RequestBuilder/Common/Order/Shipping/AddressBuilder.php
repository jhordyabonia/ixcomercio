<?php

namespace Intcomex\IngenicoRewrites\Model\Ingenico\RequestBuilder\Common\Order\Shipping;

use Ingenico\Connect\Model\Ingenico\RequestBuilder\Common\Order\AbstractAddressBuilder;
use Ingenico\Connect\Model\Ingenico\RequestBuilder\Common\Order\Shipping\Address\NameBuilder;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonal;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonalFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;

class AddressBuilder extends \Ingenico\Connect\Model\Ingenico\RequestBuilder\Common\Order\Shipping\AddressBuilder
{
    /**
     * @var AddressPersonalFactory
     */
    private $addressPersonalFactory;

    /**
     * @var NameBuilder
     */
    private $nameBuilder;

    public function __construct(AddressPersonalFactory $addressPersonalFactory, NameBuilder $nameBuilder)
    {
        $this->addressPersonalFactory = $addressPersonalFactory;
        $this->nameBuilder = $nameBuilder;
    }

    public function create(OrderInterface $order): AddressPersonal
    {
        $addressPersonal = $this->addressPersonalFactory->create();

        try {
            $shippingAddress = $this->getShippingAddressFromOrder($order);
            $this->populateAddress($addressPersonal, $shippingAddress);
            $addressPersonal->name = $this->nameBuilder->create($shippingAddress);
        } catch (LocalizedException $e) {
            //Do nothing
        }

	/*
         * GDCP: restricts to the character limit accepted by the payment gateway
        */
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/addressPersonal.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->logger->info('Shipping'); 
        
        $addressPersonal->additionalInfo    = substr($addressPersonal->additionalInfo,0,50);
        $addressPersonal->city    = substr($addressPersonal->city,0,40);
        $addressPersonal->countryCode    = substr($addressPersonal->countryCode,0,2);
        $addressPersonal->houseNumber    = substr($addressPersonal->houseNumber,0,15);
        $addressPersonal->state    = substr($addressPersonal->state,0,35);
        $addressPersonal->stateCode = substr($addressPersonal->stateCode,0,9);
        $addressPersonal->street    = substr($addressPersonal->street,0,50);
        $addressPersonal->zip    = substr($addressPersonal->zip,0,10);

        $addressPersonal->name->firstName   = substr($addressPersonal->name->firstName,0,15);
        $addressPersonal->name->surname   = substr($addressPersonal->name->surname,0,70);
        $addressPersonal->name->surnamePrefix   = substr($addressPersonal->name->surnamePrefix,0,15);
        $addressPersonal->name->title   = substr($addressPersonal->name->title,0,35);
        
        $this->logger->info(print_r($addressPersonal,true));

        return $addressPersonal;
    }

    /**
     * @param OrderInterface $order
     * @return Address
     * @throws LocalizedException
     */
    public function getShippingAddressFromOrder(OrderInterface $order): Address
    {
        if (!$order instanceof Order) {
            throw new LocalizedException(__('Can not get shipping address from OrderInterface'));
        }
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress === null) {
            throw new LocalizedException(__('No shipping address available for this order'));
        }
        return $shippingAddress;
    }
}
