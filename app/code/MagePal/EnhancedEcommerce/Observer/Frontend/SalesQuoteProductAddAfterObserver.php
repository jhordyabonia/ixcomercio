<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Observer\Frontend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;
use MagePal\GoogleTagManager\DataLayer\QuoteData\QuoteItemProvider;

/**
 * Class SalesQuoteProductAddAfterObserver
 * @package MagePal\EnhancedEcommerce\Observer\Frontend
 */
class SalesQuoteProductAddAfterObserver extends CartItemChangeAbstract implements ObserverInterface
{

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $items = $observer->getItems();
        $itemAdded = [];

        /** @var Item $item */

        foreach ($items as $item) {
            //item object data not always the same
            if (!$item->isDeleted() && !$item->getProduct()->getParentProductId() && !$item->getParentItemId()) {
                $object = $this->dataLayerItemHelper->getProductObject(
                    $item,
                    $item->getQtyToAdd()
                );

                $itemAdded[] = $this->quoteItemProvider
                                ->setItemData($object)
                                ->setItem($item)
                                ->setActionType(QuoteItemProvider::ACTION_ADDED_ITEM)
                                ->setListType(QuoteItemProvider::LIST_TYPE_GOOGLE)
                                ->getData();
            }
        }

        if (!empty($itemAdded)) {
            $this->enhancedEcommerceSession->setItemAddToCart($itemAdded);
        }
    }
}
