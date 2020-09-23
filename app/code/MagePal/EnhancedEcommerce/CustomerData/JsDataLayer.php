<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use MagePal\EnhancedEcommerce\Model\Session as EnhancedEcommerceSession;

class JsDataLayer implements SectionSourceInterface
{
    /**
     * @var EnhancedEcommerceSession
     */
    protected $enhancedEcommerceSession;

    /**
     * @param EnhancedEcommerceSession $enhancedEcommerceSession
     */
    public function __construct(
        EnhancedEcommerceSession $enhancedEcommerceSession
    ) {
        $this->enhancedEcommerceSession = $enhancedEcommerceSession;
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getSectionData()
    {
        return [
            'cartItems' => $this->enhancedEcommerceSession->getProductDataObjectArray()
        ];
    }
}
