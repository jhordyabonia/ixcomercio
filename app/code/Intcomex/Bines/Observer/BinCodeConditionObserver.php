<?php

namespace Intcomex\Bines\Observer;

use Intcomex\Bines\Model\Rule\Condition\BinCode;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BinCodeConditionObserver implements ObserverInterface
{
    /**
     * Execute observer.
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): BinCodeConditionObserver
    {
        $additional = $observer->getAdditional();
        $conditions = (array)$additional->getConditions();

        // Merging the old condition with our condition.
        $conditions = array_merge_recursive($conditions, [
            [
                'value'=> [
                    [
                        'value' => BinCode::class,
                        'label' => __(BinCode::LABEL_BIN_CODE)
                    ]
                ],
                'label'=> __(BinCode::GROUP_LABEL_BIN_CODE)
            ]
        ]);

        $additional->setConditions($conditions);
        return $this;
    }
}
