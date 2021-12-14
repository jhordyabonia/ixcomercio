<?php
declare(strict_types=1);

namespace Intcomex\Bines\Model\Bines\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Status extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**
     * Bines Status values
     */
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    const STATUS_COLUMN = 'status';

    /**
     * Retrieve Visible Status Ids
     *
     * @return int[]
     */
    public function getVisibleStatusIds(): array
    {
        return [self::STATUS_ENABLED];
    }

    /**
     * Retrieve Saleable Status Ids, default Product Enable status
     *
     * @return int[]
     */
    public function getSaleableStatusIds(): array
    {
        return [self::STATUS_ENABLED];
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function getOptionArray(): array
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions(): array
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return $options[$optionId] ?? null;
    }
}
