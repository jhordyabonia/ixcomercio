<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Cdi\Custom\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductGaleryObserver extends \Magento\ProductVideo\Observer\ChangeTemplateObserver
{
    /**
     * @param mixed $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getBlock()->setTemplate('Cdi_Custom::helper/gallery.phtml');
    }
}