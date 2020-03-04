<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Cdi\Custom\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Model\Quote;

class GuestPaymentInformationManagement extends \Magento\Checkout\Model\GuestPaymentInformationManagement
{

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    public function savePaymentInformation(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());

        if ($billingAddress) {
            //Jhonatan Holguin: Verifica y actualiza el código postal
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/postcode.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            if(!is_numeric($billingAddress->getPostcode())){
                $logger->info('Ingresa postcode (billing guest): ' . $billingAddress->getPostcode());
                $billingAddress->setPostcode('');
                $logger->info('Retorna postcode (billing guest): ' . $billingAddress->getPostcode());
            }
            $billingAddress->setEmail($email);
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->setBillingAddress($billingAddress);
            $quote->setDataChanges(true);
        } else {
            $quote->getBillingAddress()->setEmail($email);
        }
        $this->limitShippingCarrier($quote);

        $this->paymentMethodManagement->set($cartId, $paymentMethod);
        return true;
    }

    private function limitShippingCarrier(Quote $quote) : void
    {
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getShippingMethod()) {
            $shippingRate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod());
            $shippingAddress->setLimitCarrier($shippingRate->getCarrier());
        }
    }
}