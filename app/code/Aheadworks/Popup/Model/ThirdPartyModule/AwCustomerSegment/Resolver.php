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
namespace Aheadworks\Popup\Model\ThirdPartyModule\AwCustomerSegment;

use \Magento\Framework\App\Http\Context as HttpContext;
use Aheadworks\CustomerSegmentation\Model\Customer\Context as SegmentationCustomerContext;
use Aheadworks\Popup\Model\ThirdPartyModule\Manager as  ThirdPartyModuleManager;

/**
 * Class Resolver
 *
 * @package Aheadworks\Popup\Model\ThirdPartyModule\AwCustomerSegment
 */
class Resolver
{
    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var ThirdPartyModuleManager
     */
    private $thirdPartyModuleManager;

    /**
     * @param HttpContext $httpContext
     * @param ThirdPartyModuleManager $thirdPartyModuleManager
     */
    public function __construct(
        HttpContext $httpContext,
        ThirdPartyModuleManager $thirdPartyModuleManager
    ) {
        $this->httpContext = $httpContext;
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
    }

    /**
     * Retrieve list of segment ids for current customer
     *
     * @return array
     */
    public function getSegmentIdListForCurrentCustomer()
    {
        $segmentIds = [];

        if ($this->thirdPartyModuleManager->isCustomerSegmentationModuleEnabled()) {
            $contextSegmentIds = $this->httpContext->getValue(
                SegmentationCustomerContext::CONTEXT_AW_CS_SEGMENT_IDS
            );
            $segmentIds = is_array($contextSegmentIds) ? $contextSegmentIds : [];
        }

        return $segmentIds;
    }
}
