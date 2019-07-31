<?php
/**
 * Grid Schema Setup.
 * @category  Pasarela
 * @package   Pasarela_Grid
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2016 Pasarela Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Pasarela\Grid\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $conn = $setup->getConnection();
        $tableName1 = $setup->getTable('trax_match_payment');
        if($conn->isTableExists($tableName1) != true){
            $table1 = $conn->newTable($tableName1)
                    ->addColumn(
                         'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                    )
                    ->addColumn(
                        'payment_type',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable'=>false,'default'=>'']
                    )
                    ->addColumn(
                        'gateway',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullbale'=>false,'default'=>'']
                    )
                    ->addColumn(
                        'payment_code',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullbale'=>false,'default'=>'']
                    )
                    ->addColumn(
                        'trax_code',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullbale'=>false,'default'=>'']
                    )
                    ->addColumn(
                        'country_code',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullbale'=>false,'default'=>'']
                    )
                    ->addColumn(
                        'store_code',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullbale'=>false,'default'=>'']
                    )
                     ->setOption('charset','utf8');
            $conn->createTable($table1);
        }
        $setup->endSetup();
    }	
}
