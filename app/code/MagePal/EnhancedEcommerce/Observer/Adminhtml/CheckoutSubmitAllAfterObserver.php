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
use MagePal\EnhancedEcommerce\Helper\Data;
use MagePal\EnhancedEcommerce\Model\Session\Admin\Order as OrderSession;

/**
 * Google Analytics module observer
 *
 */
class CheckoutSubmitAllAfterObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var OrderSession
     */
    protected $orderSession;

    /**
     * CreditmemoPlugin constructor.
     * @param Data $eeHelper
     * @param OrderSession $orderSession
     */
    public function __construct(
        Data $eeHelper,
        OrderSession $orderSession
    ) {
        $this->helper = $eeHelper;
        $this->orderSession = $orderSession;
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
        $order = $observer->getEvent()->getOrder();

        if ($order && $this->helper->isAdminOrderTrackingEnabled($order->getStoreId())) {
            $this->orderSession->setIncrementId($order->getIncrementId());
            $this->orderSession->setOrderId($order->getId());
            $this->orderSession->setStoreId($order->getStoreId());
        }
    }
}
