<?php

namespace Magento\Pagalo\Observer;


use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\Event\Observer;


class DataAssignObserver extends AbstractDataAssignObserver
{


    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData) || !isset($additionalData[Config::PRODUCT_ID_KEY])) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/placeorder.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->logger->info(print_r($additionalData,true));


        foreach ($additionalData as $key => $value) {
            if (is_object($value)) {
                // do not try to store objects into additional information
                continue;
            }
            $paymentInfo->setAdditionalInformation(
                $key,
                $value
            );
        }
    }

}
