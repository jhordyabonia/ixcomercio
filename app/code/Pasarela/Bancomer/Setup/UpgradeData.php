<?php
/**
 *
 * @category  Pasarela
 * @package   Pasarela_Bancomer
 * @author    Pasarela
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Pasarela\Bancomer\Setup;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup; 
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

     /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param Config $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavConfig            = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);		
		if(version_compare($context->getVersion(), '1.0.1', '<')){
            $conn = $setup->getConnection();
            $tableName = $setup->getTable('bancomer_transacciones');
            if($conn->isTableExists($tableName) != true){
                $table = $conn->newTable($tableName)
                        ->addColumn(
                             'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                        )
                        ->addColumn(
                            'order_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'reference',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullbale'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'payment_method',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'payment_method_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'payment_method',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'card_type',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'bank_name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'bank_account',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'bank_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'sale_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'response',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'response_msg',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'authorization',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'date',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null,
                            ['nullbale'=>true,'default'=>'']
                        )
                         ->setOption('charset','utf8');
                $conn->createTable($table);
            }
		}
        $setup->endSetup();
    }
}