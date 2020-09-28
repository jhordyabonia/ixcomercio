<?php
namespace Cdi\Custom\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory)
	{
		$this->eavSetupFactory = $eavSetupFactory;
	}
	
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

		$statusOptions = [
			['label' => __('Si'), 'value' => 1],
			['label' => __('No'), 'value' => 0]
		];

		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Product::ENTITY,
			'activate_from_stock',
			[
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => '',
                'input' => 'select',
                'class' => '',
                'source' => $statusOptions,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '1',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'is_used_in_grid' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
		);
	}
}