<?php
/**
 * Aheadworks Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://ecommerce.aheadworks.com/end-user-license-agreement/
 *
 * @package    Popup
 * @version    1.2.3
 * @copyright  Copyright (c) 2020 Aheadworks Inc. (http://www.aheadworks.com)
 * @license    https://ecommerce.aheadworks.com/end-user-license-agreement/
 */
namespace Aheadworks\Popup\Model\Customer;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;

/**
 * Class Resolver
 *
 * @package Aheadworks\Popup\Model\Customer
 */
class Resolver
{
    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @param HttpContext $httpContext
     */
    public function __construct(
        HttpContext $httpContext
    ) {
        $this->httpContext = $httpContext;
    }

    /**
     * Retrieve group id for current customer
     *
     * @return int|null
     */
    public function getGroupIdForCurrentCustomer()
    {
        $customerGroupId = $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
        if ($customerGroupId !== null) {
            $customerGroupId = (int)$customerGroupId;
        }
        return $customerGroupId;
    }
}
