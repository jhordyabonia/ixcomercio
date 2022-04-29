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
 * @package    Bss_RobotsMetaTag
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RobotsMetaTag\Plugin\Model\RobotsTxt;

use Bss\RobotsMetaTag\Helper\Data as MetaTagHelper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class GetData
 *
 * @package Bss\RobotsMetaTag\Plugin\Model\RobotsTxt
 */
class GetData
{
    /**
     * @var MetaTagHelper
     */
    protected $metaTagHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param MetaTagHelper $metaTagHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        MetaTagHelper $metaTagHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->metaTagHelper = $metaTagHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Get the main data for robots.txt file as defined in configuration
     *
     * @return string
     */
    public function afterGetData(
        \Magento\Robots\Model\Robots $robots,
        $robotsTxt
    ) {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->metaTagHelper->getRobotsTxt($storeId, $robotsTxt);
    }
}