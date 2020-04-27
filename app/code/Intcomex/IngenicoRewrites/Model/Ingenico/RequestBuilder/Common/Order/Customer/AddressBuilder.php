<?php

namespace Intcomex\IngenicoRewrites\Model\Ingenico\RequestBuilder\Common\Order\Customer;

use Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonal;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonalFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;


class AddressBuilder extends \Ingenico\Connect\Model\Ingenico\RequestBuilder\Common\Order\Customer\AddressBuilder
{

    /**
     * @var AddressPersonalFactory
     */
    private $addressPersonalFactory;

    public function __construct(AddressPersonalFactory $addressPersonalFactory)
    {
        $this->addressPersonalFactory = $addressPersonalFactory;
    }
	
    public function create(OrderInterface $order): AddressPersonal
    {
        $addressPersonal = $this->addressPersonalFactory->create();

        try {
            $billingAddress = $this->getBillingAddressFromOrder($order);
            $this->populateAddress($addressPersonal, $billingAddress);
        } catch (LocalizedException $e) {
            //Do nothing
        }
	
	/*
	 * GDCP: restricts to the character limit accepted by the payment gateway
 	*/
	$addressPersonal->stateCode = substr($addressPersonal->stateCode,0,9);
	$addressPersonal->street    = substr($addressPersonal->street,0,50);

        return $addressPersonal;
    }
}
