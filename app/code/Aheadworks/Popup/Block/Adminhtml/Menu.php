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
namespace Aheadworks\Popup\Block\Adminhtml;

/**
 * Class Menu
 * @package Aheadworks\Popup\Block\Adminhtml
 */
class Menu extends \Magento\Backend\Block\Template
{
    const ITEM_BLOCK = 'block';
    const ITEM_README = 'readme';
    const ITEM_SUPPORT = 'support';
    const ITEM_CONFIG = 'system_config';

    /**
     * Current item key
     * @var string|null
     */
    private $currentItemKey = null;

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Popup::menu.phtml';

    /**
     * Get menu items
     *
     * @return array
     */
    public function getItems()
    {
        return [
            self::ITEM_BLOCK => [
                'title' => __('Manage Popups'),
                'url' => $this->getUrl('popup_admin/popup/index')
            ],
            self::ITEM_CONFIG => [
                'title' => __('Settings'),
                'url' => $this->getUrl('adminhtml/system_config/edit', ['section' => 'aw_popup'])
            ],
            self::ITEM_README => [
                'title' => __('Readme'),
                'url' => 'https://aheadworks.atlassian.net/wiki/spaces/EUDOC/pages/1229226551/Popup+Pro+-+Magento+2',
                'target' => '__blank',
                'class' => 'aw-extensions-menu-separator'
            ],
            self::ITEM_SUPPORT => [
                'title' => __('Get Support'),
                'url' => ' http://ecommerce.aheadworks.com/contacts/',
                'target' => '__blank',
            ],
        ];
    }

    /**
     * Get current menu item
     *
     * @return string
     */
    public function getCurrentItemKey()
    {
        return $this->currentItemKey;
    }

    /**
     * Set current menu item
     *
     * @param string $key
     *
     * @return $this
     */
    public function setCurrentItemKey($key)
    {
        $this->currentItemKey = $key;
        return $this;
    }

    /**
     * Get current menu title
     *
     * @return string
     */
    public function getCurrentItemTitle()
    {
        $items = $this->getItems();
        $key = $this->getCurrentItemKey();
        if (!array_key_exists($key, $items)) {
            return '';
        }
        return $items[$key]['title'];
    }
}
