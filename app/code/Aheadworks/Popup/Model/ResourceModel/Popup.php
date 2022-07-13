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
namespace Aheadworks\Popup\Model\ResourceModel;

use Aheadworks\Popup\Model\ResourceModel\EntityRelation\Handler\Composite;
use Aheadworks\Popup\Model\Serialize\Serializer;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Popup
 * @package Aheadworks\Popup\Model\ResourceModel
 */
class Popup extends AbstractDb
{
    /**#@+
     * Constants defined for tables
     * used by corresponding entity
     */
    const AW_CUSTOMER_SEGMENT_TABLE_NAME                                = 'aw_popup_block_segment';
    const AW_CUSTOMER_SEGMENT_TABLE_LINKAGE_FIELD_NAME                  = 'popup_id';
    const AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME   = 'segment_id';
    /**#@-*/

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Composite
     */
    private $handler;

    /**
     * @param Context $context
     * @param Serializer $serializer
     * @param Composite $handler
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        Serializer $serializer,
        Composite $handler,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->serializer = $serializer;
        $this->handler = $handler;
    }

    /**
     * Clean links to the removed AW customer segment
     *
     * @param int $removedAwCustomerSegmentId
     * @return $this
     */
    public function cleanLinksAfterAwCustomerSegmentRemoval($removedAwCustomerSegmentId)
    {
        if (!empty($removedAwCustomerSegmentId)) {
            $this
                ->getConnection()
                ->delete(
                    $this->getTable(self::AW_CUSTOMER_SEGMENT_TABLE_NAME),
                    [
                        self::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME . ' = ?' =>
                            $removedAwCustomerSegmentId
                    ]
                );
        }
        return $this;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_popup_block', 'id');
    }

    /**
     * Before save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->hasData('customer_groups') && is_array($object->getData('customer_groups'))) {
            $object->setData('customer_groups', implode(',', $object->getData('customer_groups')));
        }

        if ($object->hasData('store_ids') && is_array($object->getData('store_ids'))) {
            $object->setData('store_ids', implode(',', $object->getData('store_ids')));
        }

        if ($object->hasData('page_type') && is_array($object->getData('page_type'))) {
            $object->setData('page_type', implode(',', $object->getData('page_type')));
        }

        if (is_array($object->getData('popup_conditions'))) {
            $object->setData('product_condition', $this->serializer->serialize($object->getData('popup_conditions')));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->handler->afterSave($object);
        return parent::_afterSave($object);
    }

    /**
     * After load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $object->setData('customer_groups', explode(',', $object->getData('customer_groups')));
            $object->setData('store_ids', explode(',', $object->getData('store_ids')));

            $conditions = '';
            if ($object->getData('product_condition')) {
                $conditions = $this->serializer->unserialize($object->getData('product_condition'));
                $object->setData('conditions', $conditions);
            }
            if ($conditions) {
                $object->getRuleModel()->getConditions()->loadArray($conditions, 'popup');
            }
            $this->handler->afterLoad($object);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Add CTR to model
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $field = $this->getConnection()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
        $select = $this->getConnection()->select()->from($this->getMainTable())->where($field . '=?', $value);
        $mainTable = $this->getMainTable();
        $select->columns(
            [
                'ctr' =>
                "CONCAT(ROUND(" . $mainTable . ".click_count/IF(" . $mainTable . ".view_count > 0, "
                . $mainTable . ".view_count, 1) * 100, 0),'%')"
            ]
        );
        return $select;
    }
}
