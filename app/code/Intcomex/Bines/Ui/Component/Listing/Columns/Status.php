<?php
declare(strict_types=1);

namespace Intcomex\Bines\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Intcomex\Bines\Model\Bines\Attribute\Source\Status as BinesStatus;
use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{
    /**
     * @var BinesStatus
     */
    protected $status;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param BinesStatus $status
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        BinesStatus $status,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->status = $status;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @since 101.0.0
     */
    public function prepareDataSource(array $dataSource): array
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        $sourceFieldName = BinesStatus::STATUS_COLUMN;

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$fieldName] = $this->status->getOptionText($item[$sourceFieldName]);
        }

        return $dataSource;
    }
}
