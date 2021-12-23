<?php

namespace Intcomex\Bines\Model\Rule\Condition;

use Intcomex\Bines\Api\Data\BinesInterface;
use Intcomex\Bines\Model\Bines\Attribute\Source\Status;
use Intcomex\Bines\Model\ResourceModel\Bines\CollectionFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class BinCampaign extends AbstractCondition
{
    /**
     * Attribute campaign.
     */
    const CAMPAIGN = 'campaign';

    /**
     * Group label campaign.
     */
    const GROUP_LABEL_CAMPAIGN = 'Campaigns';

    /**
     * Label campaign.
     */
    const LABEL_CAMPAIGN = 'Bin Campaign';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Context $context,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions(): BinCampaign
    {
        $attributes = [
            self::CAMPAIGN => __(self::LABEL_CAMPAIGN)
        ];

        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Get input type.
     *
     * @return string
     */
    public function getInputType(): string
    {
        return 'multiselect';
    }

    /**
     * Get value element type.
     *
     * @return string
     */
    public function getValueElementType(): string
    {
        return 'multiselect';
    }

    /**
     * Get value select options.
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $this->setData('value_select_options', $this->getCampaignOptions());
        }
        return $this->getData('value_select_options');
    }

    /**
     * Retrieve available Campaigns.
     *
     * @return array
     */
    protected function getCampaignOptions(): array
    {
        $options = [];
        $items = $this->collectionFactory->create()
            ->addFieldToFilter(BinesInterface::STATUS, ['eq' => Status::STATUS_ENABLED])
            ->getItems();
        foreach ($items as $item) {
            $options[] = [
                'label' => $item->getCampaign(),
                'value' => $item->getId()
            ];
        }
        return $options;
    }

    /**
     * Validate Rule Condition.
     *
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model): bool
    {
        $model->setData(self::CAMPAIGN, $model->getData(self::CAMPAIGN));
        return parent::validate($model);
    }
}
