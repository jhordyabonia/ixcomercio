<?php

namespace Magento\Pagalo_Visa\Observer;


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
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagalo_visa_debug.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->logger->info(print_r($additionalData,true));

        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);



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
