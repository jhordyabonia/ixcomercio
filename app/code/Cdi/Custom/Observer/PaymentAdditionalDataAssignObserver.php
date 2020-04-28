<?php
namespace Cdi\Custom\Observer;

use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class PaymentAdditionalDataAssignObserver extends AbstractDataAssignObserver
{
    const MY_FIELD_NAME_INDEX = 'useinvoice';

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/paymentInvoice.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($additionalData);

        if (!is_array($additionalData) || !isset($additionalData[self::MY_FIELD_NAME_INDEX])) {
            return;
        }
        
        $paymentInfo = $this->readPaymentModelArgument($observer);
        $paymentInfo->setAdditionalInformation(
            self::MY_FIELD_NAME_INDEX,
            $additionalData[self::MY_FIELD_NAME_INDEX]
        );
    }
}