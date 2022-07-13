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
 * Class Event
 * @package Aheadworks\Popup\Model\Source
 */
class Event implements OptionSourceInterface
{

    const PAGE_LOADED = 'immediately_page_loaded';
    const AFTER_DURATION = 'x_sec_after_duration';
    const PAGE_SCROLLED = 'once_page_scrolled';
    const VIEWED_PAGES = 'once_customer_pages_viewed';
    const OUTSIDE_BROWSER = 'once_cursor_leave_browser';

    const DEFAULT_VALUE = 'x_sec_after_duration';

    const VIEWED_PAGE_COUNT_COOKIE_NAME = 'aw_popup_viewed_page';
    const VIEWED_POPUP_COUNT_COOKIE_NAME = 'aw_popup_viewed_popup';
    const USED_POPUP_COUNT_COOKIE_NAME = 'aw_popup_used_popup';

    const DEFAULT_EVENT_X_VALUE = 60;
    const DEFAULT_COOKIE_LIFETIME_VALUE = 1440;

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $newArray = [];
        $positions = $this->toOptionArray();

        foreach ($positions as $position) {
            $newArray[$position['value']] = $position['label'];
        }
        return $newArray;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PAGE_LOADED,  'label' => __('Immediately after the page is loaded (not recommended)')],
            ['value' => self::AFTER_DURATION,  'label' => __('X seconds after the page is loaded')],
            ['value' => self::PAGE_SCROLLED,  'label' => __('Once the page is scrolled by X%')],
            ['value' => self::VIEWED_PAGES,  'label' => __('Once customer viewed X pages')],
            ['value' => self::OUTSIDE_BROWSER,  'label' => __('Once cursor is moved outside the page')],
        ];
    }
}
