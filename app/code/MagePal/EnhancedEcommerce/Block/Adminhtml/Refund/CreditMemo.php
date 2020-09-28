<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Adminhtml\Refund;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use MagePal\GoogleTagManager\Model\DataLayerEvent;

class CreditMemo extends Template
{
    /**
     * EE Helper
     *
     * @var \MagePal\EnhancedEcommerce\Helper\Data
     */
    protected $eeHelper;

    /**
     * @var \MagePal\EnhancedEcommerce\Model\Session\CreditMemo
     */
    protected $creditMemoSession;

    /**
     * @var int
     */
    protected $store_id = null;

    /**
     * @param Context $context
     * @param \MagePal\EnhancedEcommerce\Helper\Data $eeHelper
     * @param \MagePal\EnhancedEcommerce\Model\Session\Admin\CreditMemo $creditMemoSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        \MagePal\EnhancedEcommerce\Helper\Data $eeHelper,
        \MagePal\EnhancedEcommerce\Model\Session\Admin\CreditMemo $creditMemoSession,
        array $data = []
    ) {
        $this->eeHelper = $eeHelper;
        $this->creditMemoSession = $creditMemoSession;

        $this->store_id = $creditMemoSession->getStoreId();
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->eeHelper->isEnabled($this->store_id)
            || !$this->creditMemoSession->getOrderId()
            || $this->store_id != $this->creditMemoSession->getGtmAccountStoreId()
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
            'event' => DataLayerEvent::REFUND_EVENT,
            'ecommerce' => [
                'refund' => [
                    'actionField' => [
                        'id' => $this->creditMemoSession->getIncrementId()
                    ],
                    'products' => $this->creditMemoSession->getproducts()
                ],
                'currencyCode' => $this->creditMemoSession->getBaseCurrencyCode()
            ]
        ];

        $revenue = $this->creditMemoSession->getAmount();
        if ($revenue) {
            $refundJson['ecommerce']['refund']['actionField']['revenue'] = $revenue;
        }

        $this->creditMemoSession->clearStorage();

        return json_encode($refundJson);
    }
}
