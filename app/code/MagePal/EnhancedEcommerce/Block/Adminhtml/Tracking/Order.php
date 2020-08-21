<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Adminhtml\Tracking;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use MagePal\EnhancedEcommerce\Helper\Data;
use MagePal\EnhancedEcommerce\Model\Order as OrderModel;
use MagePal\EnhancedEcommerce\Model\Session\Admin\Order as OrderSession;

/**
 * Class Order
 * @package MagePal\EnhancedEcommerce\Block\Adminhtml\Tracking
 */
class Order extends Template
{
    /**
     * EE Helper
     *
     * @var Data
     */
    protected $eeHelper;

    /**
     * @var OrderSession
     */
    protected $orderSession;

    /**
     * @var OrderModel
     */
    protected $gtmOrderObject;

    /**
     * @var int
     */
    protected $store_id = null;

    /**
     * @param Context $context
     * @param Data $eeHelper
     * @param OrderSession $orderSession
     * @param OrderModel $gtmOrderObject
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $eeHelper,
        OrderSession $orderSession,
        OrderModel $gtmOrderObject,
        array $data = []
    ) {
        $this->eeHelper = $eeHelper;
        $this->orderSession = $orderSession;
        $this->gtmOrderObject = $gtmOrderObject;

        $this->store_id = $orderSession->getStoreId();
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->eeHelper->isEnabled($this->store_id)
            || !$this->orderSession->getOrderId()
            || $this->store_id != $this->orderSession->getGtmAccountStoreId()
        ) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getDataLayerName()
    {
        return $this->eeHelper->getDataLayerName($this->store_id);
    }

    /**
     * @return null|string
     * @throws NoSuchEntityException
     */
    public function getJsonData()
    {
        $this->gtmOrderObject->setOrderIds([$this->orderSession->getOrderId()]);
        $orderDataLayer = $this->gtmOrderObject->getOrderLayer();
        $this->orderSession->clearStorage();

        $result = '{}';

        if (is_array($orderDataLayer)) {
            foreach ($orderDataLayer as $custom) {
                $result = sprintf("%s", json_encode($custom));
            }
        }

        return $result;
    }
}
