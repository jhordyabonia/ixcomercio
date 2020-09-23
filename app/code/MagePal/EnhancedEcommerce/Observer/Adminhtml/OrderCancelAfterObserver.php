<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use MagePal\EnhancedEcommerce\Helper\Data;
use MagePal\EnhancedEcommerce\Model\Session\Admin\CancelOrder;

/**
 * Google Analytics module observer
 *
 */
class OrderCancelAfterObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CancelOrder
     */
    protected $cancelOrderSession;

    /**
     * CreditmemoPlugin constructor.
     * @param Data $eeHelper
     * @param CancelOrder $cancelOrderSession
     */
    public function __construct(
        Data $eeHelper,
        CancelOrder $cancelOrderSession
    ) {
        $this->helper = $eeHelper;
        $this->cancelOrderSession = $cancelOrderSession;
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /* @var Order $order */
        $order = $observer->getOrder();

        if ($order && $this->helper->isRefundEnabled($order->getStoreId())) {
            $this->cancelOrderSession->setOrderId($order->getId());
            $this->cancelOrderSession->setIncrementId($order->getIncrementId());
            $this->cancelOrderSession->setBaseCurrencyCode($order->getBaseCurrencyCode());
            $this->cancelOrderSession->setStoreId($order->getStoreId());
            $this->cancelOrderSession->setAmount($order->getBaseGrandTotal());

            $products = [];
            /** @var Item $item */
            foreach ($order->getAllVisibleItems() as $item) {
                $products[]= [
                    'id' => $item->getSku(),
                    'quantity' => $item->getQty() * 1,
                ];
            }

            $this->cancelOrderSession->setProducts($products);
        }
    }
}
