<?php

namespace Intcomex\CredomaticMSI\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    const CAMPAIGN_TABLE_NAME = 'campaign';
    const CAMPAIGN_DETAIL_TABLE_NAME = 'campaign_detail';


    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $campaignTable = $setup->getConnection()->newTable(
            $setup->getTable(self::CAMPAIGN_TABLE_NAME)
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true]
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false]
        )->addColumn(
            'start_date',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false]
        )->addColumn(
            'end_date',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false]
        )->addColumn(
            'status',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false]
        );

        $setup->getConnection()->createTable($campaignTable);

        $detailCampaignTable = $setup->getConnection()->newTable(
            $setup->getTable(self::CAMPAIGN_DETAIL_TABLE_NAME)
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true]
        )->addColumn(
            'campaign_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'sku',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false]
        )->addColumn(
            'fee',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'max_units',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'hash',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false]
        )->addColumn(
            'status',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false]
        )->addForeignKey(
            "campaign_campaign_detail",
            'campaign_id',
            $setup->getTable(self::CAMPAIGN_TABLE_NAME),
            'id',
            Table::ACTION_CASCADE
        )->addIndex(
            $setup->getIdxName(
                self::CAMPAIGN_TABLE_NAME,
                ['hash'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['hash'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        );

        $setup->getConnection()->createTable($detailCampaignTable);

        $setup->endSetup();
    }
}
