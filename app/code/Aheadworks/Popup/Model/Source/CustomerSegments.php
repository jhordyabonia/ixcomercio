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

namespace Aheadworks\Popup\Model\Source;

use Aheadworks\Popup\Model\ThirdPartyModule\Manager;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CustomerSegments
 * @package Aheadworks\Popup\Model\Source
 */
class CustomerSegments implements OptionSourceInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Manager $moduleManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $moduleManager
    ) {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        $options = [];

        if ($this->moduleManager->isCustomerSegmentationModuleEnabled()) {
            $collection = $this->getSegmentsCollection();
            $collection->addOrder('name', SortOrder::SORT_ASC);
            /** @var \Aheadworks\CustomerSegmentation\Api\Data\SegmentInterface $segment */
            foreach ($collection->getItems() as $segment) {
                $options[] = [
                    'value' => $segment->getSegmentId(),
                    'label' => $segment->getIsEnabled()
                        ? $segment->getName()
                        : $segment->getName() . __('(Disabled)')
                ];
            }
        }

        return $options;
    }

    /**
     * Retrieve segments collection
     *
     * @return \Aheadworks\CustomerSegmentation\Model\ResourceModel\Segment\Collection
     */
    private function getSegmentsCollection()
    {
        return $this->objectManager->create(
            \Aheadworks\CustomerSegmentation\Model\ResourceModel\Segment\Collection::class
        );
    }
}
