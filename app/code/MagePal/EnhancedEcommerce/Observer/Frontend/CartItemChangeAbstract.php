<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Observer\Frontend;

use Magento\Quote\Model\Quote\Item;
use MagePal\EnhancedEcommerce\Model\Session as EnhancedEcommerceSession;
use MagePal\GoogleTagManager\DataLayer\QuoteData\QuoteItemProvider;
use MagePal\GoogleTagManager\Helper\DataLayerItem;

/**
 * Class CartItemChangeAbstract
 * @package MagePal\EnhancedEcommerce\Observer\Frontend
 */
class CartItemChangeAbstract
{

    /**
     * @var EnhancedEcommerce
     */
    protected $enhancedEcommerceSession;

    /**
     * @var QuoteItemProvider
     */
    protected $quoteItemProvider;

    /**
     * @var DataLayerItem
     */
    protected $dataLayerItemHelper;

    protected $updatedQtyItems =  [];

    protected $deletedQtyItems = [];

    /**
     * @param EnhancedEcommerceSession $enhancedEcommerceSession
     * @param DataLayerItem $dataLayerItemHelper
     * @param QuoteItemProvider $quoteItemProvider
     */
    public function __construct(
        EnhancedEcommerceSession $enhancedEcommerceSession,
        DataLayerItem  $dataLayerItemHelper,
        QuoteItemProvider $quoteItemProvider
    ) {
        $this->enhancedEcommerceSession = $enhancedEcommerceSession;
        $this->quoteItemProvider = $quoteItemProvider;
        $this->dataLayerItemHelper = $dataLayerItemHelper;
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param Item $items
     * @return $this
     */
    public function processItems($items)
    {
        $this->updatedQtyItems = [];
        $this->deletedQtyItems = [];

        /** @var Item $item */
        foreach ($items as $item) {
            $this->processItem($item);
        }

        if (!empty($this->updatedQtyItems)) {
            $this->enhancedEcommerceSession->setItemAddToCart($this->updatedQtyItems);
        }

        if (!empty($this->deletedQtyItems)) {
            $this->enhancedEcommerceSession->setItemRemovedFromCart($this->deletedQtyItems);
        }

        return $this;
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param Item $item
     * @return $this
     */
    public function processItem($item)
    {
        if ($item->isDeleted()) {
            $object = $this->dataLayerItemHelper->getProductObject(
                $item,
                $item->getQty()
            );

            $this->deletedQtyItems[] = $this->quoteItemProvider
                ->setItemData($object)
                ->setItem($item)
                ->setActionType(QuoteItemProvider::ACTION_REMOVED_ITEM)
                ->setListType(QuoteItemProvider::LIST_TYPE_GOOGLE)
                ->getData();
        } elseif ($item->getQty() < $item->getOrigData('qty')) {
            $qty = $item->getOrigData('qty') - $item->getQty();

            $object = $this->dataLayerItemHelper->getProductObject(
                $item,
                $qty
            );

            $this->deletedQtyItems[] = $this->quoteItemProvider
                ->setItemData($object)
                ->setItem($item)
                ->setActionType(QuoteItemProvider::ACTION_REMOVED_ITEM)
                ->setListType(QuoteItemProvider::LIST_TYPE_GOOGLE)
                ->getData();
        } elseif ($item->getQty() > $item->getOrigData('qty')) {
            $qty = $item->getQty() - $item->getOrigData('qty');

            $object = $this->dataLayerItemHelper->getProductObject(
                $item,
                $qty
            );

            $this->updatedQtyItems[] =  $this->quoteItemProvider
                ->setItemData($object)
                ->setItem($item)
                ->setActionType(QuoteItemProvider::ACTION_UPDATED_ITEM)
                ->setListType(QuoteItemProvider::LIST_TYPE_GOOGLE)
                ->getData();
        }

        return $this;
    }
}
