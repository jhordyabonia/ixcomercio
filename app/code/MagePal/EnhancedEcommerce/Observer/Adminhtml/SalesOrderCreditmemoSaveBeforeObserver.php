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
use Magento\Sales\Model\Order\Creditmemo\Item;
use MagePal\EnhancedEcommerce\Helper\Data;
use MagePal\EnhancedEcommerce\Model\Session\Admin\CreditMemo;

/**
 * Google Analytics module observer
 *
 */
class SalesOrderCreditmemoSaveBeforeObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CreditMemo
     */
    protected $creditMemoSession;

    /**
     * CreditmemoPlugin constructor.
     * @param Data $eeHelper
     * @param CreditMemo $creditMemoSession
     */
    public function __construct(
        Data $eeHelper,
        CreditMemo $creditMemoSession
    ) {
        $this->helper = $eeHelper;
        $this->creditMemoSession = $creditMemoSession;
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
        $creditMemo = $observer->getCreditmemo();

        if ($creditMemo && $creditMemo->isObjectNew() && $this->helper->isRefundEnabled($creditMemo->getStoreId())) {
            $this->creditMemoSession->setOrderId($creditMemo->getOrder()->getId());
            $this->creditMemoSession->setIncrementId($creditMemo->getOrder()->getIncrementId());
            $this->creditMemoSession->setBaseCurrencyCode($creditMemo->getBaseCurrencyCode());
            $this->creditMemoSession->setStoreId($creditMemo->getStoreId());
            $this->creditMemoSession->setAmount($creditMemo->getBaseGrandTotal());

            $products = [];
            /** @var Item $item */
            foreach ($creditMemo->getItems() as $item) {
                if (!$item->getOrderItem()->isDeleted() && !$item->getOrderItem()->getParentItemId()) {
                    $qty = $item->getQty() * 1;
                    if ($qty > 0) {
                        $products[]= [
                            'id' => $item->getSku(),
                            'quantity' => $qty,
                        ];
                    }
                }
            }

            $this->creditMemoSession->setProducts($products);
        }
    }
}
