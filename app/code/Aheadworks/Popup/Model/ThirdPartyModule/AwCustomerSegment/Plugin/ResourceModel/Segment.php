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
namespace Aheadworks\Popup\Model\ThirdPartyModule\AwCustomerSegment\Plugin\ResourceModel;

use Aheadworks\Popup\Model\ResourceModel\Popup as PopupResourceModel;
use Aheadworks\CustomerSegmentation\Model\ResourceModel\Segment as SegmentResourceModel;
use Aheadworks\CustomerSegmentation\Model\Segment as SegmentModel;

/**
 * Class Segment
 *
 * @package Aheadworks\Popup\Model\ThirdPartyModule\AwCustomerSegment\Plugin\ResourceModel
 */
class Segment
{
    /**
     * @var PopupResourceModel
     */
    private $popupResourceModel;

    /**
     * @param PopupResourceModel $popupResourceModel
     */
    public function __construct(
        PopupResourceModel $popupResourceModel
    ) {
        $this->popupResourceModel = $popupResourceModel;
    }

    /**
     * Clean links between popup and the removed AW customer segment
     *
     * @param SegmentResourceModel $subject
     * @param SegmentResourceModel $result
     * @param SegmentModel $segment
     * @return SegmentResourceModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        SegmentResourceModel $subject,
        SegmentResourceModel $result,
        SegmentModel $segment
    ) {
        if ($segment && $segment->getId()) {
            $this->popupResourceModel->cleanLinksAfterAwCustomerSegmentRemoval($segment->getId());
        }
        return $result;
    }
}
