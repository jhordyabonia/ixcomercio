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
namespace Aheadworks\Popup\Model\ResourceModel\Popup;

use Aheadworks\Popup\Model\Popup;
use Aheadworks\Popup\Model\Source\Event;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Aheadworks\Popup\Model\ResourceModel\Popup as PopupResource;

/**
 * Class Collection
 * @package Aheadworks\Popup\Model\ResourceModel\Popup
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends AbstractCollection
{
    /**
     * Id field name
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * {@inheritDoc}
     */
    public function _construct()
    {
        $this->_init(Popup::class, PopupResource::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        if (!$this->getFlag('ctr_joined')) {
            $this->getSelect()
                ->joinLeft(
                    ['ctr_t' => new \Zend_Db_Expr(
                        "(Select " .
                        "id as ctr_id, " .
                        "ROUND(click_count/IF(view_count > 0, view_count, 1) * 100, 0) as ctr ".
                        "FROM {$this->getTable('aw_popup_block')})"
                    )
                    ],
                    "main_table.id = ctr_t.ctr_id",
                    ['ctr']
                );
            $this->addFilterToMap('ctr', 'ctr_t.ctr');
            $this->setFlag('ctr_joined', true);
        }
        $this->joinSegmentsData();

        return $this;
    }

    /**
     * Add customer group filter
     *
     * @param array $customerGroups
     * @return $this
     */
    public function addCustomerGroupFilter($customerGroups)
    {
        $this->addFieldToFilter('customer_groups', ['finset' => $customerGroups]);
        return $this;
    }

    /**
     * Add position filter
     *
     * @param int $position
     * @return $this
     */
    public function addPositionFilter($position)
    {
        $this->addFieldToFilter('position', ['eq' => $position]);
        return $this;
    }

    /**
     * Add page type filter
     *
     * @param int $page
     * @return $this
     */
    public function addPageTypeFilter($page)
    {
        $this->addFieldToFilter('page_type', ['finset' => $page]);
        return $this;
    }

    /**
     * Add store filter
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $this
            ->getSelect()
            ->where("FIND_IN_SET(0, store_ids) OR FIND_IN_SET({$storeId}, store_ids)");
        return $this;
    }

    /**
     * Add status enabled filter
     *
     * @return $this
     */
    public function addStatusEnabledFilter()
    {
        $this->addFieldToFilter('status', ['eq' => 1]);
        return $this;
    }

    /**
     * Add excluded ids filter
     *
     * @param array $popupIds
     * @return $this
     */
    public function addExcludedIdsFilter(array $popupIds)
    {
        $this->addFieldToFilter('main_table.id', ['nin' => $popupIds]);
        return $this;
    }

    /**
     * Add pages viewed filter
     *
     * @param int $viewedCount
     * @return $this
     */
    public function addPageViewedFilter($viewedCount)
    {
        $eventPageViewedType = Event::VIEWED_PAGES;
        $this
            ->getSelect()
            ->where(
                "(main_table.event <> '" . $eventPageViewedType . "' OR (main_table.event = '" . $eventPageViewedType .
                "' AND main_table.event_value <= " . $viewedCount . "))"
            );
        return $this;
    }

    /**
     * Add filter by customer segment id list from AW
     *
     * @param array $segmentIdList
     * @return $this
     */
    public function addCustomerSegmentFilter($segmentIdList)
    {
        $this->addFilter(
            PopupResource::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME,
            [
                ['null' => true],
                ['in' => $segmentIdList]
            ],
            'public'
        );
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function _afterLoad()
    {
        $this->attachSegmentsData();

        return parent::_afterLoad();
    }

    /**
     * Attach customer segments data
     *
     * @return $this
     */
    private function attachSegmentsData()
    {
        $select = $this->getConnection()
            ->select()
            ->from(
                $this->getTable(PopupResource::AW_CUSTOMER_SEGMENT_TABLE_NAME)
            )
        ;
        $segmentsData = $this->getConnection()->fetchAll($select);

        foreach ($this->getItems() as $item) {
            $dataToAttach = [];
            $popupId = $item->getId();
            foreach ($segmentsData as $segmentData) {
                if ($segmentData[PopupResource::AW_CUSTOMER_SEGMENT_TABLE_LINKAGE_FIELD_NAME] == $popupId) {
                    $dataToAttach[] =
                        $segmentData[PopupResource::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME];
                }
            }
            $item->setCustomerSegments($dataToAttach);
        }

        return $this;
    }

    /**
     * Join customer segments data for filtering
     *
     * @return $this
     */
    private function joinSegmentsData()
    {
        if (!$this->getFlag('is_segments_data_joined')) {
            $this->getSelect()
                ->joinLeft(
                    [
                        'segmentsDataTable' => $this->getTable(PopupResource::AW_CUSTOMER_SEGMENT_TABLE_NAME)
                    ],
                    "main_table." . $this->getIdFieldName()
                    . " = segmentsDataTable." . PopupResource::AW_CUSTOMER_SEGMENT_TABLE_LINKAGE_FIELD_NAME,
                    [
                        PopupResource::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME
                    ]
                )->group(
                    "main_table." . $this->getIdFieldName()
                )
            ;
            $this->addFilterToMap(
                PopupResource::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME,
                "segmentsDataTable." . PopupResource::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME
            );
            $this->setFlag('is_segments_data_joined', true);
        }
        return $this;
    }
}
