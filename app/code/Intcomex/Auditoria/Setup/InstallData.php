<?php

namespace Intcomex\Auditoria\Setup;

use Magento\Eav\Setup\EavSetup; 
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
	private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'precio_referencia', [
                'type'     => 'varchar',
                'input'    => 'text',
                'source'   => '',
                'visible'  => false,
                'required' => false,
                'default'  => '',
                'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            ]);			
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'moneda', [
                'type'     => 'varchar',
                'input'    => 'text',
                'source'   => '',
                'visible'  => false,
                'required' => false,
                'default'  => '',
                'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            ]);			

        $setup->endSetup();
	}
}