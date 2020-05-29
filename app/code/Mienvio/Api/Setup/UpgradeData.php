<?php

namespace Mienvio\Api\Setup;

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
		if(version_compare($context->getVersion(), '0.0.1', '<')){
            $conn = $setup->getConnection();
            $setup->getConnection()->addColumn(
				$setup->getTable( 'iws_order' ),
				'mienvio_guide',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					'nullable' => false,
					'default' => 0,
					'comment' => 'Indicates if the guide has been generated',
					'after' => 'iws_order'
				]
			);
            $setup->getConnection()->addColumn(
				$setup->getTable( 'iws_order' ),
				'mienvio_delivery',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					'nullable' => false,
					'default' => 0,
					'comment' => 'Indicates if the order has been delivered',
					'after' => 'mienvio_guide'
				]
			);
        }

        if(version_compare($context->getVersion(), '0.0.2', '<')){
            $conn = $setup->getConnection();
            $setup->getConnection()->addColumn(
				$setup->getTable( 'iws_order' ),
				'mienvio_upload_resp',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'default' => '',
					'comment' => 'json response from mienvio::upload'
				]
			);
            $setup->getConnection()->addColumn(
				$setup->getTable('iws_order'),
				'mienvio_update_resp',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'default' => '',
					'comment' => 'json response from mienvio::update'
				]
			);
		}

		if(version_compare($context->getVersion(), '0.0.3', '<')){
            $conn = $setup->getConnection();
            $setup->getConnection()->addColumn(
				$setup->getTable( 'iws_order' ),
				'trax_invoice',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'default' => '',
					'comment' => 'json response from trax'
				]
			);
		}
        $setup->endSetup();
    }
}