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
 * @package    Bss_Breadcrumbs
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Breadcrumbs\Plugin;

class GetBlockBreadcrumbsObject
{
    /**
     * @var \Bss\Breadcrumbs\Block\Breadcrumbs
     */
    protected $breadcrumbs;

    /**
     * GetBlockBreadcrumbsObject constructor.
     * @param \Bss\Breadcrumbs\Block\Breadcrumbs $breadcrumbs
     */
    public function __construct(
        \Bss\Breadcrumbs\Block\Breadcrumbs $breadcrumbs
    ) {
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * @param \Bss\Breadcrumbs\ViewModel\Product\Breadcrumbs $breadcrumbs
     * @param $result
     * @return \Bss\Breadcrumbs\Block\Breadcrumbs
     */
    public function afterGetBlockObject(
        \Bss\Breadcrumbs\ViewModel\Product\Breadcrumbs $breadcrumbs,
        $result
    ) {
        if (!$result || !($result instanceof \Bss\Breadcrumbs\Block\Breadcrumbs)) {
            return $this->breadcrumbs;
        }
        return $result;
    }
}
