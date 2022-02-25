<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Cdi\Custom\Setup;

use Magento\Eav\Setup\EavSetup; 
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as eavAttribute;
 
 
class UpgradeData implements UpgradeDataInterface{
 	private $eavSetupFactory;
	private $attributeSetFactory;
	private $attributeSet;
	private $categorySetupFactory;
	protected $_attributeSetCollection;
	private $eavConfig;
 
   	public function __construct(
		EavSetupFactory $eavSetupFactory, 
		AttributeSetFactory $attributeSetFactory, 
		CategorySetupFactory $categorySetupFactory,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollection,
		\Magento\Eav\Model\Config $eavConfig
	){
		$this->eavSetupFactory = $eavSetupFactory; 
		$this->attributeSetFactory = $attributeSetFactory; 
		$this->categorySetupFactory = $categorySetupFactory;
		$this->_attributeSetCollection = $attributeSetCollection;
		$this->eavConfig = $eavConfig;
	} 
	
 	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
		$setup->startSetup();
		$attsToAdd = array();
		
		if(version_compare($context->getVersion(), '1.0.3', '<')){
			$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			$categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
			$entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
			$attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
			$setName = 'Jam';
			
			// CREATE ATTRIBUTE SET 
			$attsetId = $this->getAttrSetId($setName);
			if(!$attsetId){
				$attributeSet = $this->attributeSetFactory->create();
				$data = [
					'attribute_set_name' => $setName, 
					'entity_type_id' => $entityTypeId,
					'sort_order' => 200,
				];
				$attributeSet->setData($data);
				$attributeSet->validate();
				$attributeSet->save();
				$attributeSet->initFromSkeleton($attributeSetId);
				$attributeSet->save();
			}
			
			// DELETE OLD
			$eavSetup->removeAttribute($entityTypeId, 'map_image_link');
			// CREATE PRODUCT ATTRIBUTE
			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Product::ENTITY,
				'map_image_link',
				[
					'group' => 'Jam attributes',
					'type' => 'varchar',
					'label' => 'Category image coords',
					'backend' => '',
					'input' => 'text',
					'wysiwyg_enabled' => false,
					'source' => '',
					'required' => false,
					'sort_order' => 5,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'attribute_set' => $setName,
				]
			);
		}
		
		if(version_compare($context->getVersion(), '1.0.4', '<')){
			$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			$entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
			$eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'cat_attributes', [
				'type'     => 'text',
				'label'    => 'Category attributes',
				'input'    => 'text',
				'source'   => '',
				'frontend' => 'Cdi\Custom\Model\Attribute\Frontend\Attributes',
                'backend' => 'Cdi\Custom\Model\Attribute\Backend\Attributes',
				'visible'  => true,
				'default'  => '0',
				'required' => false,
				'user_defined' => true,
				'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
				'group'    => 'Display Settings',
			]);			
		}
		
		if(version_compare($context->getVersion(), '1.0.8', '<')){
			// ADD ATTRIBUTES
			$attsToAdd['map_image_link'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'Category image coords',
					'backend' => '',
					'input' => 'text',
					'wysiwyg_enabled' => false,
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'simple',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Jam Attributes',
					'sort_order' => 2,
				)
			);
			$attsToAdd['specs_img1'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'Quick specs - imagen 1',
					'input' => 'media_image',
					'wysiwyg_enabled' => false,
					'frontend' => 'Magento\Catalog\Model\Product\Attribute\Frontend\Image',
					'backend' => '',
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Jam Attributes',
					'sort_order' => 5,
				)
			);
			$attsToAdd['thebox_img1'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'In the box - imagen 1',
					'input' => 'media_image',
					'wysiwyg_enabled' => false,
					'frontend' => 'Magento\Catalog\Model\Product\Attribute\Frontend\Image',
					'backend' => '',
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Jam Attributes',
					'sort_order' => 5,
				)
			);
			$attsToAdd['thebox_img2'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'In the box - imagen 2',
					'input' => 'media_image',
					'wysiwyg_enabled' => false,
					'frontend' => 'Magento\Catalog\Model\Product\Attribute\Frontend\Image',
					'backend' => '',
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Jam Attributes',
					'sort_order' => 5,
				)
			);
			$attsToAdd['depth'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'Depth',
					'backend' => '',
					'input' => 'text',
					'wysiwyg_enabled' => false,
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Product Details',
					'sort_order' => 71,
				)
			);
			$attsToAdd['height'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'Height',
					'backend' => '',
					'input' => 'text',
					'wysiwyg_enabled' => false,
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Product Details',
					'sort_order' => 72,
				)
			);
			$attsToAdd['width'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'Width',
					'backend' => '',
					'input' => 'text',
					'wysiwyg_enabled' => false,
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Product Details',
					'sort_order' => 73,
				)
			);
		}
			
		if(version_compare($context->getVersion(), '1.0.11', '<')){
			// ADD ATTRIBUTES
			$attsToAdd['register_jam'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'Register your JAM link',
					'backend' => '',
					'input' => 'text',
					'wysiwyg_enabled' => false,
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Jam Attributes',
					'sort_order' => 8,
				)
			);
			// ADD ATTRIBUTES
			$attsToAdd['in_the_box'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'In the box',
					'backend' => '',
					'input' => 'text',
					'wysiwyg_enabled' => false,
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Jam Attributes',
					'sort_order' => 9,
				)
			);
		}
		
		if(version_compare($context->getVersion(), '1.0.14', '<')){
			// ADD ATTRIBUTES
			$attsToAdd['color_swatch_att'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY
			);
			// ADD ATTRIBUTES
			$attsToAdd['quickstart'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'Quick Start',
					'backend' => '',
					'input' => 'text',
					'wysiwyg_enabled' => false,
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Jam Attributes',
					'sort_order' => 6,
				)
			);
			// ADD ATTRIBUTES
			$attsToAdd['warranty_link'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'Warranty link',
					'backend' => '',
					'input' => 'text',
					'wysiwyg_enabled' => false,
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Jam Attributes',
					'sort_order' => 7,
				)
			);
		}
		
		if(version_compare($context->getVersion(), '1.0.15', '<')){
			// ADD ATTRIBUTES
			$attsToAdd['instrucctions'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'varchar',
					'label' => 'Instructions',
					'backend' => '',
					'input' => 'text',
					'wysiwyg_enabled' => false,
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Jam Attributes',
					'sort_order' => 5,
				)
			);
		}
		
		if(version_compare($context->getVersion(), '1.0.16', '<')){
			// ADD ATTRIBUTES
			$attsToAdd['product_attributes'] = array(
				'entity' => \Magento\Catalog\Model\Product::ENTITY,
				'attdata' => array(
					'type' => 'text',
					'label' => 'Extra attributes',
					'backend' => '',
					'input' => 'text',
					'wysiwyg_enabled' => false,
					'source' => '',
					'required' => false,
					'user_defined' => true,
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'apply_to' => 'configurable',
				),
				'group' => array(
					'attribute_set' => 'Jam',
					'group' => 'Extra Attributes',
					'sort_order' => 7,
				)
			);
		}

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();

		if(version_compare($context->getVersion(), '1.0.17', '<')){			
			$tableName = $resource->getTableName('theme');
			//Verifica si existe el theme whitelabel
			$sql = "SELECT * FROM {$tableName} where code = 'Cdi/whitelabel'";
			$result = $connection->fetchAll($sql); 
			if($result && count($result)){
				$id = (isset($result[0]['theme_id'])) ? $result[0]['theme_id'] : false;
				if($id){
					$themes = "('Cdi/hpca', 'Cdi/lvgt')";
					$sql = "UPDATE {$tableName} SET parent_id = {$id} WHERE code IN {$themes}";
					$connection->query($sql);
				}
			}
		}

		if(version_compare($context->getVersion(), '1.0.18', '<')){
			$tableName = $resource->getTableName('directory_country_region');
			$sql = "DELETE FROM {$tableName} where country_id = 'MX'";
			$connection->query($sql);
		}

		if(version_compare($context->getVersion(), '1.0.19', '<')){
			$quote = 'quote';
			$orderTable = 'sales_order';
			$attributeOrder = 'useinvoice';
			$setup->getConnection()
			->addColumn(
				$setup->getTable($quote),
				$attributeOrder,
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => 50,
					'comment' =>'RequireInvoice'
				]
			);
			$setup->getConnection()
			->addColumn(
				$setup->getTable($orderTable),
				'',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
					'nullable' =>false,
					'comment' =>'Is Paid'
				]
			);
			//Order table
			$setup->getConnection()
			->addColumn(
				$setup->getTable($orderTable),
				$attributeOrder,
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => 50,
					'comment' =>'RequireInvoice'
				]
			);

		}
		
		if(count($attsToAdd)){
			$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			foreach($attsToAdd as $attcode => $data){
				$categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
				$entityTypeId = $categorySetup->getEntityTypeId($data['entity']);
				// DELETE OLD
				$eavSetup->removeAttribute($entityTypeId, $attcode);
				// CREATE ATTRIBUTE
				if(isset($data['attdata'])){
					$eavSetup->addAttribute($data['entity'], $attcode, $data['attdata']);
				}
				// ADD ATTRIBUTE TO GROUP
				if(isset($data['group'])){
					$eavSetup->addAttributeToGroup(
						$data['entity'],
						$data['group']['attribute_set'],
						$data['group']['group'],
						$attcode,
						$data['group']['sort_order']
					);					
				}				
			}
		}
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'activate_from_stock');
		
		$setup->endSetup();
	}
	
	public function getAttrSetId($attrSetName){
		$attributeSetId = false;
        $attributeSet = $this->_attributeSetCollection->create()->addFieldToSelect(
			'*'
		)->addFieldToFilter(
			'attribute_set_name',
			$attrSetName
		);
        foreach($attributeSet as $attr):
            $attributeSetId = $attr->getAttributeSetId();
        endforeach;
        return $attributeSetId;
    }
	
	
}