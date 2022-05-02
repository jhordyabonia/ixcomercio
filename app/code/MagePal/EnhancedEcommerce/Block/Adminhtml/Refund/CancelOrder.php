<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Adminhtml\Refund;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session;
use MagePal\EnhancedEcommerce\Helper\Data;
use MagePal\EnhancedEcommerce\Model\Session\Admin\CancelOrder as CancelOrderSession;
use MagePal\GoogleTagManager\Model\DataLayerEvent;

class CancelOrder extends Template
{
    /**
     * EE Helper
     *
     * @var Data
     */
    protected $eeHelper;

    /**
     * @var Session
     */
    protected $cancelOrderSession;

    /**
     * @var int
     */
    protected $store_id = null;

    /**
     * @param Context $context
     * @param Data $eeHelper
     * @param CancelOrderSession $cancelOrderSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $eeHelper,
        CancelOrderSession $cancelOrderSession,
        array $data = []
    ) {
        $this->eeHelper = $eeHelper;
        $this->cancelOrderSession = $cancelOrderSession;

        $this->store_id = $cancelOrderSession->getStoreId();
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->eeHelper->isEnabled($this->store_id)
            || !$this->cancelOrderSession->getOrderId()
            || $this->store_id != $this->cancelOrderSession->getGtmAccountStoreId()
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
     */
    public function getJsonData()
    {
        $refundJson = [
            'environment' => DataLayerEvent::ENVIRONMENT,
            'event' => DataLayerEvent::REFUND_EVENT,
            'ecommerce' => [
                'refund' => [
                    'actionField' => [
                        'id' => $this->cancelOrderSession->getIncrementId()
                    ]
                ]
            ]
        ];

        $this->cancelOrderSession->clearStorage();

        return json_encode($refundJson);
    }
}
