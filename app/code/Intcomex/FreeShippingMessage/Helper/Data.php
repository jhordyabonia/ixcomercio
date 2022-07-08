<?php
declare(strict_types=1);

namespace Intcomex\FreeShippingMessage\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @const Free Shipping Data.
     */
    const FREE_SHIPPING_MESSAGE_GENERAL_ENABLED = 'free_shipping_message/general/enabled';
    const FREE_SHIPPING_MESSAGE_GENERAL_AMOUNT = 'free_shipping_message/general/amount';
    const FREE_SHIPPING_MESSAGE_GENERAL_MESSAGE = 'free_shipping_message/general/message';
    const FREE_SHIPPING_MESSAGE_GENERAL_SUCCESS_MESSAGE = 'free_shipping_message/general/successmessage';

    /**
     * @param null $store
     * @return bool
     */
    public function isEnabled($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::FREE_SHIPPING_MESSAGE_GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getAmount($store = null)
    {
        return $this->scopeConfig->getValue(
            self::FREE_SHIPPING_MESSAGE_GENERAL_AMOUNT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getMessage($store = null)
    {
        return $this->scopeConfig->getValue(
            self::FREE_SHIPPING_MESSAGE_GENERAL_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getSuccessMessage($store = null)
    {
        return $this->scopeConfig->getValue(
            self::FREE_SHIPPING_MESSAGE_GENERAL_SUCCESS_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
