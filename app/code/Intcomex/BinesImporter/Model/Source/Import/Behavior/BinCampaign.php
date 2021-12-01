<?php
declare(strict_types=1);

namespace Intcomex\BinesImporter\Model\Source\Import\Behavior;

use Magento\ImportExport\Model\Import;

class BinCampaign extends \Magento\ImportExport\Model\Source\Import\AbstractBehavior
{
    /**
     * @const Behavior code.
     */
    const BEHAVIOR_CODE = 'intcomex_bin_campaign';

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_APPEND => __('Add/Update')
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return self::BEHAVIOR_CODE;
    }

    /**
     * @inheritdoc
     */
    public function getNotes($entityCode)
    {
        $messages = [
            self::BEHAVIOR_CODE => [
                Import::BEHAVIOR_APPEND => __('New campaign data is added to the existing data for the existing entries in the database.')
            ]
        ];
        return isset($messages[$entityCode]) ? $messages[$entityCode] : [];
    }
}
