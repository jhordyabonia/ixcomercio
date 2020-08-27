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
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($additionalData as $key => $value) {
	      // error_log("Evaluando $additionalInformationKey");

           if (is_object($value)) {
               continue;
           }
           
           $paymentInfo->setAdditionalInformation(
                    $key,
                    $value
                );
        }
    }

/*


    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);
        $paymentInfo = $method->getInfoInstance();




        if ($data->getDataByKey('my_number') !== null) {
            $paymentInfo->setAdditionalInformation(
                'my_number',
                $data->getDataByKey('my_number')
            );
        }

    }


*/



}
