<?php

namespace Trax\Catalogo\Setup;

use Magento\Eav\Setup\EavSetup; 
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);		
		if(version_compare($context->getVersion(), '1.0.2', '<')){
            $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'iws_id', [
                'type'     => 'varchar',
                'label'    => 'Id IWS',
                'input'    => 'text',
                'source'   => '',
                'visible'  => true,
                'required' => false,
                'default'  => '',
                'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'    => 'Display Settings',
            ]);			
        }
        
        if(version_compare($context->getVersion(), '1.0.3', '<')){
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'iws_type', [
                'type'     => 'varchar',
                'label'    => 'Type IWS',
                'input'    => 'text',
                'source'   => '',
                'visible'  => true,
                'required' => false,
                'default'  => '',
                'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'    => 'Display Settings',
            ]);			
		}

        $setup->endSetup();
    }
}