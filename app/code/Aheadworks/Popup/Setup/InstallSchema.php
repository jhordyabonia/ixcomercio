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

namespace Aheadworks\Popup\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Aheadworks\Popup\Setup\Updater\Schema\Updater as SchemaUpdater;

/**
 * Class InstallSchema
 * @package Aheadworks\Popup\Setup
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var SchemaUpdater
     */
    private $schemaUpdater;

    /**
     * @param SchemaUpdater $schemaUpdater
     */
    public function __construct(
        SchemaUpdater $schemaUpdater
    ) {
        $this->schemaUpdater = $schemaUpdater;
    }
    
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->createBlockTable($installer);
        $this->schemaUpdater->update120($installer);

        $installer->endSetup();
    }

    /**
     * Create popup block table
     *
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    public function createBlockTable(SchemaSetupInterface $installer)
    {
        /** @var Table $blockTable */
        $blockTable = $installer->getConnection()->newTable($installer->getTable('aw_popup_block'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Popup ID'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Popup Name'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'customer_groups',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Customer Groups'
            )
            ->addColumn(
                'store_ids',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Store IDs'
            )
            ->addColumn(
                'page_type',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Popup Type'
            )
            ->addColumn(
                'position',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Position'
            )
            ->addColumn(
                'event',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Event'
            )
            ->addColumn(
                'event_value',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'X value for event'
            )
            ->addColumn(
                'effect',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Effect'
            )
            ->addColumn(
                'cookie_lifetime',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Cookie Lifetime'
            )
            ->addColumn(
                'content',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Content'
            )
            ->addColumn(
                'custom_css',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Custom CSS'
            )
            ->addColumn(
                'product_condition',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Product Condition'
            )
            ->addColumn(
                'category_ids',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Category IDs'
            )
            ->addColumn(
                'view_count',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Popup view count'
            )
            ->addColumn(
                'click_count',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Popup click count'
            );

        $installer->getConnection()->createTable($blockTable);
    }
}
