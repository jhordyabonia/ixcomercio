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
namespace Aheadworks\Popup\Model\ResourceModel\EntityRelation\Handler;

use Aheadworks\Popup\Model\Popup;
use Aheadworks\Popup\Model\ResourceModel\EntityRelation\HandlerInterface;
use Magento\Framework\App\ResourceConnection;
use Aheadworks\Popup\Model\ResourceModel\Popup as PopupResourceModel;

/**
 * Class CustomerSegments
 * @package Aheadworks\Popup\Model\ResourceModel\EntityRelation\Handler
 */
class CustomerSegments implements HandlerInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritDoc}
     * @param Popup $entity
     */
    public function afterSave($entity)
    {
        if ($entity->getId()) {
            $this->deleteByEntity($entity->getId());
            $dataToSave = $this->getDataToSave($entity);
            $this->saveData($dataToSave);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterLoad($entity)
    {
        if ($entity->getId()) {
            $segments = $this->getSegmentsData($entity);
            $entity->setCustomerSegments($segments);
        }
    }

    /**
     * Get table name
     *
     * @return string
     */
    private function getTableName()
    {
        return $this->resourceConnection->getTableName(PopupResourceModel::AW_CUSTOMER_SEGMENT_TABLE_NAME);
    }

    /**
     * Remove data
     *
     * @param int $popupId
     * @return int
     */
    private function deleteByEntity($popupId)
    {
        return $this->resourceConnection->getConnection()->delete(
            $this->getTableName(),
            [PopupResourceModel::AW_CUSTOMER_SEGMENT_TABLE_LINKAGE_FIELD_NAME . ' = ?' => $popupId]
        );
    }

    /**
     * Retrieve data to save in the corresponding table
     *
     * @param Popup $entity
     * @return array
     */
    private function getDataToSave($entity)
    {
        $data = [];
        $popupId = $entity->getId();

        foreach ((array)$entity->getCustomerSegments() as $segmentId) {
            $data[] = [
                PopupResourceModel::AW_CUSTOMER_SEGMENT_TABLE_LINKAGE_FIELD_NAME => $popupId,
                PopupResourceModel::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME => $segmentId
            ];
        }

        return $data;
    }

    /**
     * Save data in the corresponding table
     *
     * @param array $dataToSave
     * @return $this
     */
    private function saveData($dataToSave)
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->getTableName();
            $connection->insertMultiple(
                $tableName,
                $dataToSave
            );
        } catch (\Exception $exception) {
            return $this;
        }

        return $this;
    }

    /**
     * Retrieve segments data
     *
     * @param Popup $entity
     * @return array
     */
    public function getSegmentsData($entity)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                $this->getTableName(),
                [PopupResourceModel::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME]
            )->where(PopupResourceModel::AW_CUSTOMER_SEGMENT_TABLE_LINKAGE_FIELD_NAME . ' = ?', $entity->getId());

        return $connection->fetchCol($select);
    }
}
