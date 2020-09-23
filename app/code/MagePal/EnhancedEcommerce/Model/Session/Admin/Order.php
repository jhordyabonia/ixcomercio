<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Model\Session\Admin;

use Magento\Framework\Session\SessionManager;

class Order extends SessionManager
{
    /**
     * @param $orderId
     * @return $this
     */
    public function setStoreId($orderId)
    {
        $this->setData('store_id', $orderId);
        return $this;
    }

    /**
     * @param bool $clear
     * @return string
     */
    public function getStoreId($clear = false)
    {
        return $this->getData('store_id', $clear);
    }

    /**
     * @param $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->setData('order_id', $orderId);
        return $this;
    }

    /**
     * @param bool $clear
     * @return string
     */
    public function getOrderId($clear = false)
    {
        return $this->getData('order_id', $clear);
    }

    /**
     * @param $orderId
     * @return $this
     */
    public function setIncrementId($orderId)
    {
        $this->setData('increment_id', $orderId);
        return $this;
    }

    /**
     * @param bool $clear
     * @return string
     */
    public function getIncrementId($clear = false)
    {
        return $this->getData('increment_id', $clear);
    }

    /**
     * @param $id
     * @return $this
     */
    public function setGtmAccountStoreId($id)
    {
        $this->setData('gtm_account_store_id', $id);
        return $this;
    }

    /**
     * @param bool $clear
     * @return int
     */
    public function getGtmAccountStoreId($clear = false)
    {
        return $this->getData('gtm_account_store_id', $clear);
    }
}
