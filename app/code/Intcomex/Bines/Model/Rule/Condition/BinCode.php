<?php

namespace Intcomex\Bines\Model\Rule\Condition;

use Intcomex\Bines\Model\ResourceModel\Bines\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class BinCode extends AbstractCondition
{
    /**
     * Attribute bin code.
     */
    const BIN_CODE = 'bin_code';

    /**
     * Group label bin code.
     */
    const GROUP_LABEL_BIN_CODE = 'Bin Campaign';

    /**
     * Label bin code.
     */
    const LABEL_BIN_CODE = 'Bin Code';

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
    public function loadAttributeOptions(): BinCode
    {
        $attributes = [
            self::BIN_CODE => __(self::LABEL_BIN_CODE)
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
     * @throws LocalizedException
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $this->setData('value_select_options', $this->getBinCodeOptions());
        }
        return $this->getData('value_select_options');
    }

    /**
     * Retrieve available Bin Codes.
     *
     * @return array
     */
    protected function getBinCodeOptions(): array
    {
        $options = [];
        $items = $this->collectionFactory->create()
            ->addFieldToFilter('status', ['eq' => 1])
            ->getItems();
        foreach ($items as $item) {
            $options[] = [
                'label' => $item->getBinCode(),
                'value' => $item->getBinCode()
            ];
        }
        return $options;
    }
}
