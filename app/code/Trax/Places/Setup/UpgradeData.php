<?php
/**
 *
 * @category  Trax
 * @package   Trax_Places
 * @author    Trax
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Trax\Places\Setup;

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
		if(version_compare($context->getVersion(), '1.0.3', '<')){
            $conn = $setup->getConnection();
            $tableName = $setup->getTable('trax_places_regions');
            $tableName1 = $setup->getTable('trax_places_cities');
            $tableName2 = $setup->getTable('trax_places_localities');
            if($conn->isTableExists($tableName) != true){
                $table = $conn->newTable($tableName)
                        ->addColumn(
                             'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                        )
                        ->addColumn(
                            'store_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'country_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable'=>false,'default'=>'']
                        )
                        ->addColumn(
                             'trax_id',
                             \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                             255,
                             ['nullbale'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'level',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'parent_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'area_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'postal_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'status',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['nullable'=>false,'default'=>1]
                        )
                         ->setOption('charset','utf8');
                $conn->createTable($table);
            }
            if($conn->isTableExists($tableName1) != true){
                $table1 = $conn->newTable($tableName1)
                        ->addColumn(
                             'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                        )
                        ->addColumn(
                            'store_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'country_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'trax_places_region_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable'=>false,'default'=>'']
                        )
                        ->addColumn(
                             'trax_id',
                             \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                             255,
                             ['nullbale'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'level',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'parent_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'area_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'postal_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'status',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['nullable'=>false,'default'=>1]
                        )
                         ->setOption('charset','utf8');
                $conn->createTable($table1);
            }
            if($conn->isTableExists($tableName2) != true){
                $table2 = $conn->newTable($tableName2)
                        ->addColumn(
                             'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                        )
                        ->addColumn(
                            'store_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'country_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'trax_places_city_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable'=>false,'default'=>'']
                        )
                        ->addColumn(
                             'trax_id',
                             \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                             255,
                             ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'level',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'parent_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'area_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'postal_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>true,'default'=>'']
                        )
                        ->addColumn(
                            'status',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['nullable'=>false,'default'=>1]
                        )
                         ->setOption('charset','utf8');
                $conn->createTable($table2);
            }
        }	
        
        if(version_compare($context->getVersion(), '1.0.3', '<')){
            $conn = $setup->getConnection();
            $tableCountry = $setup->getTable('trax_places_country');           

            if($conn->isTableExists($tableCountry) != true){
                $table = $conn->newTable($tableCountry)
                        ->addColumn(
                             'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                        )
                        ->addColumn(
                            'country_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable'=>false,'default'=>'']
                        )                        
                        ->addColumn(
                            'name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullbale'=>true,'default'=>'']
                        )                        
                        ->addColumn(
                            'status',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['nullable'=>false,'default'=>1]
                        )
                         ->setOption('charset','utf8');
                $conn->createTable($table);
            }
        }
        $setup->endSetup();
    }
}