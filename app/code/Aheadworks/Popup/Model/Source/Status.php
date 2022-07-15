<?php
/**
 * Aheadworks Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://ecommerce.aheadworks.com/end-user-license-agreement/
 *
 * @package    Popup
 * @version    1.2.3
 * @copyright  Copyright (c) 2020 Aheadworks Inc. (http://www.aheadworks.com)
 * @license    https://ecommerce.aheadworks.com/end-user-license-agreement/
 */

namespace Aheadworks\Popup\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Aheadworks\Popup\Model\Source
 */
class Status implements OptionSourceInterface
{
    /**
     * Status values
     */
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    /**
     * Get option array
     *
     * @return array
     * phpcs:disable Magento2.Functions
     */
    public static function getOptionArray()
    {
        return [self::STATUS_ENABLED => __('Enable'), self::STATUS_DISABLED => __('Disable')];
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STATUS_ENABLED,  'label' => __('Enabled')],
            ['value' => self::STATUS_DISABLED,  'label' => __('Disabled')],
        ];
    }

    /**
     * To option array for mass status
     *
     * @return array
     */
    public function toOptionArrayForMassStatus()
    {
        return [
            ['value' => self::STATUS_ENABLED,  'label' => __('Enable')],
            ['value' => self::STATUS_DISABLED,  'label' => __('Disable')],
        ];
    }
}
