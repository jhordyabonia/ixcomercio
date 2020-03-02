<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Cdi\Custom\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface as Logger;

class PaymentInformationManagement extends \Magento\Checkout\Model\PaymentInformationManagement
{

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    public function savePaymentInformation(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($billingAddress) {
            //Jhonatan Holguin: Verifica y actualiza el código postal
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            if(!is_numeric($billingAddress->getPostcode())){
                $logger->info('Ingresa postcode: ' . $billingAddress->getPostcode());
                $billingAddress->setPostcode('');
                $logger->info('Retorna postcode: ' . $billingAddress->getPostcode());
            }
            /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
            $quoteRepository = $this->getCartRepository();
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $quoteRepository->getActive($cartId);
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->setBillingAddress($billingAddress);
            $quote->setDataChanges(true);
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress && $shippingAddress->getShippingMethod()) {
                $shippingRate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod());
                $shippingAddress->setLimitCarrier(
                    $shippingRate ? $shippingRate->getCarrier() : $shippingAddress->getShippingMethod()
                );
            }
        }
        $this->paymentMethodManagement->set($cartId, $paymentMethod);
        return true;
    }

    private function getCartRepository()
    {
        if (!$this->cartRepository) {
            $this->cartRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        }
        return $this->cartRepository;
    }
}