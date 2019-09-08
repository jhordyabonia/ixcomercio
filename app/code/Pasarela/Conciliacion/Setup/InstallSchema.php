<?php
/**
 * Conciliacion Schema Setup.
 * @category  Pasarela
 * @package   Pasarela_Conciliacion
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2016 Pasarela Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Pasarela\Conciliacion\Setup;

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
        $tableName1 = $setup->getTable('bancomer_conciliation');
        if($conn->isTableExists($tableName1) != true){
            $table1 = $conn->newTable($tableName1)
                    ->addColumn(
                         'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                    )
                    ->addColumn(
                        'conciliation_date',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                        'conciliation_date'
                    )
                    ->addColumn(
                        'procesed_payments',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        ['nullbale'=>false,'default'=>0]
                    )
                    ->addColumn(
                        'procesed_orders',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullbale'=>false,'default'=>'']
                    )
                    ->addColumn(
                        'unprocesed_orders',
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
