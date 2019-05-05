<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Sitemap
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Sitemap\Model\Source;

use Magento\Cms\Model\PageFactory;

/**
 * Class Page
 * @package Mageplaza\Sitemap\Model\Source
 */
class Page
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * Page constructor.
     *
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     */
    public function __construct(PageFactory $pageFactory)
    {
        $this->_pageFactory = $pageFactory;
    }

    /**
     * Get list cms pages
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $collection = $this->_pageFactory->create()->getCollection();
        foreach ($collection as $item) {
            $options[] = ['value' => $item->getIdentifier(), 'label' => $item->getTitle()];
        }

        return $options;
    }
}