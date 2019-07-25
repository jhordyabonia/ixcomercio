<?php
/**
 *
 * @category  Trax
 * @package   Trax_Ordenes
 * @author    Trax
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Trax\Ordenes\Setup;

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
		if(version_compare($context->getVersion(), '1.0.2', '<')){
            $eavSetup->addAttribute('customer_address', 'identificacion', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'Identificación',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'system'=> false,
                'group'=> 'General',
                'global' => true,
                'visible_on_front' => true,
            ]);	
       
            $customAttribute = $this->eavConfig->getAttribute('customer_address', 'identificacion');
    
            $customAttribute->setData(
                'used_in_forms',
                ['adminhtml_customer_address','customer_address_edit','customer_register_address']
            );
            $customAttribute->save();
		}	
		if(version_compare($context->getVersion(), '1.0.3', '<')){
            $conn = $setup->getConnection();
            $tableName = $setup->getTable('iws_order');
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
                            'order_increment_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullbale'=>false,'default'=>'']
                        )
                        ->addColumn(
                            'iws_order',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '2M',
                            ['nullbale'=>false,'default'=>'']
                        )
                         ->setOption('charset','utf8');
                $conn->createTable($table);
            }
		}	
        $setup->endSetup();
    }
}