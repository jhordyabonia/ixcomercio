<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MagePal\EnhancedEcommerce\Helper\Data;

/**
 * Class JsComponent
 * @package MagePal\EnhancedEcommerce\Block
 */
class JsComponent extends Template
{
    /** @var Data */
    protected $_eeHelper;

    /**
     * @param Context $context
     * @param Data $eeHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $eeHelper,
        array $data = []
    ) {
        $this->_eeHelper = $eeHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_eeHelper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getDataLayerName()
    {
        return $this->_eeHelper->getDataLayerName();
    }
}
