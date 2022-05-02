<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\XmlSiteMap\Plugin\Model\Menu;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\ItemFactory;

/**
 * Create XML Sitemap menu programmatically in case SeoCore module dose not enabled
 *
 *
 * @package Bss\XmlSiteMap\Plugin\Model\Menu
 */
class BuilderPlugin
{
    /**
     * @var ItemFactory
     */
    private $menuItemFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * BuilderPlugin constructor.
     * @param ItemFactory $menuItemFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        ItemFactory $menuItemFactory,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->menuItemFactory = $menuItemFactory;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param Builder $subject
     * @param Menu $menu
     * @return Menu
     */
    public function afterGetResult(Builder $subject, Menu $menu)
    {
        $sortOrder = 60;
        if ($this->moduleManager->isEnabled('Bss_SeoCore')) {
            // add menu item to Bss_SeoCore marketing menu
            /** @var Menu\Item $item */
            $parent = 'Bss_SeoCore::bss_seo';
            $item = $this->menuItemFactory->create([
                'data' => [
                    'parent_id' => $parent,
                    'id' => 'Bss_XmlSiteMap::bss_sitemap',
                    'title' => 'Google XML Sitemap',
                    'resource' => 'Bss_XmlSiteMap::bss_sitemap',
                    'sort_index' => '60',
                    'action' => 'adminhtml/xmlsitemap/',
                ]
            ]);

            $menu->add($item, $parent, $sortOrder);
        } else {
            // Add new parent menu in marketing menu
            $parent = 'Magento_Backend::marketing';
            $item = $this->menuItemFactory->create([
                'data' => [
                    'parent_id' => $parent,
                    'id' => 'Bss_XmlSiteMap::bss_seo',
                    'title' => 'BSS Commerce SEO',
                    'resource' => 'Bss_XmlSiteMap::main',
                ]
            ]);
            $menu->add($item, $parent, $sortOrder);

            // Add child menu in BSS Commerce SEO menu
            $parent = 'Bss_XmlSiteMap::bss_seo';
            $item = $this->menuItemFactory->create([
                'data' => [
                    'parent_id' => $parent,
                    'id' => 'Bss_XmlSiteMap::bss_sitemap',
                    'title' => 'Google XML Sitemap',
                    'resource' => 'Bss_XmlSiteMap::bss_sitemap',
                    'action' => 'adminhtml/xmlsitemap/',
                ]
            ]);
            $menu->add($item, $parent, $sortOrder);
        }

        return $menu;
    }
}
