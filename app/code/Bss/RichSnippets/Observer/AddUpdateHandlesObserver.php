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
 * @package    Bss_RichSnippets
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RichSnippets\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddUpdateHandlesObserver
 * @package Bss\RichSnippets\Observer
 */
class AddUpdateHandlesObserver implements ObserverInterface
{
    const XML_PATH_ENABLE = 'bss_richsnippets/general/enable';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Add New Layout handle
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $layout = $observer->getData('layout');
        $fullActionName = $observer->getData('full_action_name');

        if ($fullActionName != 'catalog_product_view') {
            return $this;
        }

        $enable = $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($enable == '1') {
            $layout->getUpdate()->addHandle('bss_richsnippets_remove');
        }
        return $this;
    }
}
