<?php

namespace Intcomex\Bines\Observer;

use Intcomex\Bines\Model\Rule\Condition\BinCampaign;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BinCampaignConditionObserver implements ObserverInterface
{
    /**
     * Execute observer.
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): BinCampaignConditionObserver
    {
        $additional = $observer->getAdditional();
        $conditions = (array)$additional->getConditions();

        // Merging the old condition with our condition.
        $conditions = array_merge_recursive($conditions, [
            [
                'value'=> [
                    [
                        'value' => BinCampaign::class,
                        'label' => __(BinCampaign::LABEL_CAMPAIGN)
                    ]
                ],
                'label'=> __(BinCampaign::GROUP_LABEL_CAMPAIGN)
            ]
        ]);

        $additional->setConditions($conditions);
        return $this;
    }
}
