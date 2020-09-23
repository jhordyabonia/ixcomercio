<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use MagePal\GoogleTagManager\DataLayer\OrderData\OrderItemProvider;
use MagePal\GoogleTagManager\Model\DataLayerEvent;

/**
 * Class Order
 * @package MagePal\EnhancedEcommerce\Model
 * @method Array setOrderIds(Array $orderIds)
 * @method Array getOrderIds()
 */
class Order extends \MagePal\GoogleTagManager\Model\Order
{
    /**
     * Render information about specified orders and their items
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrderLayer()
    {
        $collection = $this->getOrderCollection();

        if (!$collection) {
            return false;
        }

        $result = [];

        /* @var \Magento\Sales\Model\Order $order */
        $purchase = [];
        foreach ($collection as $order) {
            $products = [];
            foreach ($order->getAllVisibleItems() as $item) {
                $product = [
                    'id' => $item->getSku(),
                    'parent_sku' => $item->getProduct()->getData('sku'),
                    'name' => $this->escapeJsQuote($item->getName()),
                    'price' => $this->gtmHelper->formatPrice($item->getBasePrice()),
                    'quantity' => $item->getQtyOrdered() * 1,
                    //'brand' => ''
                ];

                if ($variant = $this->dataLayerItemHelper->getItemVariant($item)) {
                    $product['variant'] = $variant;
                }

                if ($category = $this->dataLayerItemHelper->getFirstCategory($item)) {
                    $product['category'] = $category;
                }

                $products[] = $this->orderItemProvider
                                ->setItem($item)
                                ->setItemData($product)
                                ->setListType(OrderItemProvider::LIST_TYPE_GOOGLE)
                                ->getData();
            }

            $purchase['purchase']['actionField'] = [
                'id' => $order->getIncrementId(),
                'affiliation' => $this->escapeJsQuote($order->getStoreName()),
                'revenue' => $this->gtmHelper->formatPrice($order->getBaseGrandTotal()),
                'tax' => $this->gtmHelper->formatPrice($order->getTaxAmount()),
                'shipping' => $this->gtmHelper->formatPrice($order->getBaseShippingAmount()),
                'coupon' => $order->getCouponCode() ? $order->getCouponCode() : ''
            ];

            $purchase['purchase']['products'] = $products;

            $transaction = [
                'event' => DataLayerEvent::PURCHASE_EVENT,
                'ecommerce' => $purchase,
                'order' => $this->getOrderDataLayer($order)
            ];

            $result[] = $this->orderProvider->setOrder($order)->setTransactionData($transaction)->getData();
        }

        return $result;
    }
}
