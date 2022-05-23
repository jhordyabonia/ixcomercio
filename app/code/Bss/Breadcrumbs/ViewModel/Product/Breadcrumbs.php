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
namespace Bss\Breadcrumbs\ViewModel\Product;

class Breadcrumbs extends \Magento\Catalog\ViewModel\Product\Breadcrumbs
{
    /**
     * @var \Bss\Breadcrumbs\Block\Breadcrumbs|null
     */
    protected $breadcrumbsBlock;

    /**
     * @return \Bss\Breadcrumbs\Block\Breadcrumbs|null
     */
    public function getBlockObject() {
        return null;
    }

    /**
     * @return \Bss\Breadcrumbs\Block\Breadcrumbs
     */
    public function getBreadcrumbsBlock()
    {
        $breadcrumbsBlock = $this->getBlockObject();
        $widgetOptions = $this->getJsonConfigurationHtmlEscaped();
        $breadcrumbsBlock->setData('widget_options', $widgetOptions);
        return $breadcrumbsBlock;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCrumbs()
    {
        $breadcrumbsBlock = $this->getBlockObject();
        return $breadcrumbsBlock->getCrumbsProduct();
    }
}
