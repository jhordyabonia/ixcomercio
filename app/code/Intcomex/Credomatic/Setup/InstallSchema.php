<?php

namespace Mageplaza\HelloWorld\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		$setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'is_paid',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                ['default' => 0],
                'comment' => 'Id Paid'
            ]
        );
		$installer->endSetup();
	}
}