<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-11-25T09:30:14+00:00
 * File:          Setup/UpgradeSchema.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '2.3.9', '<')) {
            $connection->addIndex(
                $setup->getTable('xtento_productexport_profile_history'),
                $setup->getIdxName('xtento_productexport_profile_history', ['entity_id']),
                ['entity_id']
            );
        }
        if (version_compare($context->getVersion(), '2.7.3', '<')) {
            $connection->addColumn(
                $setup->getTable('xtento_productexport_profile'),
                'category_mapping',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => 16777215,
                    'comment' => 'Category Mapping'
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.7.4', '<')) {
            $connection->addColumn(
                $setup->getTable('xtento_productexport_profile'),
                'taxonomy_source',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => 255,
                    'comment' => 'Taxonomy Source'
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.8.5', '<')) {
            $connection->addColumn(
                $setup->getTable('xtento_productexport_profile'),
                'remove_pub_folder_from_urls',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => true,
                    'length' => 1,
                    'comment' => 'Remove pub folder from URLs'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.10.3', '<')) {
            $connection->changeColumn(
                $setup->getTable('xtento_productexport_destination'),
                'port',
                'port',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 5,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Port'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.11.9', '<')) {
            // Move cronjobs into separate cron group
            $connection->query(
                "UPDATE " . $setup->getTable('core_config_data') . " 
                    SET path = REPLACE(path, 'crontab/default/jobs/" . \Xtento\ProductExport\Cron\Export::CRON_GROUP . "', 'crontab/" . \Xtento\ProductExport\Cron\Export::CRON_GROUP . "/jobs/" . \Xtento\ProductExport\Cron\Export::CRON_GROUP . "')
                    WHERE path LIKE 'crontab/default/jobs/" . \Xtento\ProductExport\Cron\Export::CRON_GROUP . "%'"
            );
        }

        if (version_compare($context->getVersion(), '2.12.6', '<')) {
            $connection->addColumn(
                $setup->getTable('xtento_productexport_destination'),
                'ftp_ignorepasvaddress',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => false,
                    'length' => 1,
                    'comment' => 'FTP Ignore PASV Address'
                ]
            );
        }

        $setup->endSetup();
    }
}
