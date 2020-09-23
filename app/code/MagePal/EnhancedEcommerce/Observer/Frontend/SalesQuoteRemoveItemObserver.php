<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Observer\Frontend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SalesQuoteRemoveItemObserver
 * @package MagePal\EnhancedEcommerce\Observer\Frontend
 */
class SalesQuoteRemoveItemObserver extends CartItemChangeAbstract implements ObserverInterface
{

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $items[] = $observer->getQuoteItem();
        $this->processItems($items);
    }
}
