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
 
 
class UpgradeData implements UpgradeDataInterface{
 	private $eavSetupFactory;
	private $attributeSetFactory;
	private $attributeSet;
	private $categorySetupFactory;
	protected $_attributeSetCollection;
 
   	public function __construct(
		EavSetupFactory $eavSetupFactory, 
		AttributeSetFactory $attributeSetFactory, 
		CategorySetupFactory $categorySetupFactory,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollection
	){
		$this->eavSetupFactory = $eavSetupFactory; 
		$this->attributeSetFactory = $attributeSetFactory; 
		$this->categorySetupFactory = $categorySetupFactory;
		$this->_attributeSetCollection = $attributeSetCollection;
	} 
	
 	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
		$setup->startSetup();
		
		if(version_compare($context->getVersion(), '1.0.3', '<')){
			$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			$categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
			$entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
			$attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
			$setName = 'Jam';
			//DELETE OLD
			$eavSetup->removeAttribute($entityTypeId, 'map_image_link');
			
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
				$attsetId = $attributeSet->getAttributeSetId();
			}
	 
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
					'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
					'used_in_product_listing' => true,
					'visible_on_front' => true,
					'attribute_set_id' => $attsetId,
				]
			); 
		}
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
	
} ?>