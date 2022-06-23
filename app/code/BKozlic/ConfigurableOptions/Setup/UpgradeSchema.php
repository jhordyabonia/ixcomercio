<?php 

namespace BKozlic\ConfigurableOptions\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{


protected $customerSetupFactory;

public function __construct(
    EavSetupFactory $eavSetupFactory,
    ModuleDataSetupInterface $moduleDataSetup
) {
    $this->eavSetupFactory = $eavSetupFactory;
    $this->moduleDataSetup = $moduleDataSetup;
}

public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {

    $setup->startSetup();

    if (version_compare($context->getVersion(), '1.0.5') < 0) {

        $setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'simple_product_preselect',
            [
                'label'         => 'Simple Product',
                'global'        => ScopedAttributeInterface::SCOPE_STORE,
                'visible'       => 0,
                'required'      => 0,
                'user_defined'  => 1,
                'group'         => 'General',
                'apply_to'      => 'configurable',
            ]
        );

        $setup->endSetup();

     }

   }
}