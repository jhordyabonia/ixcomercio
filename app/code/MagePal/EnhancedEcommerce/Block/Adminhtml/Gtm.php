<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use MagePal\EnhancedEcommerce\Helper\Data;
use MagePal\EnhancedEcommerce\Model\Session\Admin\CancelOrder;
use MagePal\EnhancedEcommerce\Model\Session\Admin\CreditMemo;
use MagePal\EnhancedEcommerce\Model\Session\Admin\Order;

class Gtm extends Template
{
    /**
     * EE Helper
     *
     * @var Data
     */
    protected $eeHelper;

    /**
     * @var CancelOrder
     */
    protected $cancelOrderSession;

    /**
     * @var Order
     */
    protected $orderSession;

    /**
     * @var CreditMemo
     */
    protected $creditMemoSession;

    /**
     * @var int
     */
    protected $store_id = null;

    /**
     * @var array
     */
    protected $gtmAccountStoreIds = [];

    /**
     * @var bool
     */
    protected $showTrackingCode = false;

    /**
     * @param Context $context
     * @param CancelOrder $cancelOrderSession
     * @param CreditMemo $creditMemoSession
     * @param Order $orderSession
     * @param Data $eeHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CancelOrder $cancelOrderSession,
        CreditMemo $creditMemoSession,
        Order $orderSession,
        Data $eeHelper,
        array $data = []
    ) {
        $this->cancelOrderSession = $cancelOrderSession;
        $this->creditMemoSession = $creditMemoSession;
        $this->orderSession = $orderSession;
        $this->eeHelper = $eeHelper;

        if ($this->cancelOrderSession->getOrderId()) {
            $this->gtmAccountStoreIds[] = $this->cancelOrderSession->getStoreId();
        }

        if ($this->creditMemoSession->getOrderId()) {
            $this->gtmAccountStoreIds[] = $this->creditMemoSession->getStoreId();
        }

        if ($this->orderSession->getOrderId()) {
            $this->gtmAccountStoreIds[] = $this->orderSession->getStoreId();
        }

        if (count($this->gtmAccountStoreIds) > 0) {
            $this->store_id = array_pop($this->gtmAccountStoreIds);
            $this->cancelOrderSession->setGtmAccountStoreId($this->store_id);
            $this->creditMemoSession->setGtmAccountStoreId($this->store_id);
            $this->orderSession->setGtmAccountStoreId($this->store_id);
            $this->showTrackingCode = true;
        }

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getDataLayerName()
    {
        return $this->eeHelper->getDataLayerName($this->store_id);
    }

    public function getAccountId()
    {
        return $this->eeHelper->getAccountId($this->store_id);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->showTrackingCode) {
            return '';
        }

        return parent::_toHtml();
    }
}
