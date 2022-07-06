<?php

namespace Intcomex\CredomaticMSI\Model;

use Intcomex\CredomaticMSI\Model\ResourceModel\Campaign\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class CampaignOptions implements OptionSourceInterface
{
    /**
     * @var CollectionFactory $collectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $collection = $this->collectionFactory->create();

        foreach($collection as $block){
            $options[] = [
                'value' => $block->getData('id'),
                'label' => $block->getData('description')
            ];
        }
        return $options;
    }
}
