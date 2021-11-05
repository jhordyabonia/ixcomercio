<?php

namespace Intcomex\CustomLog\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
    {
      protected $triggerFactory;

        public function __construct(
            \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory
        )
        {
            $this->triggerFactory = $triggerFactory;
        }

        public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
        {
            $installer = $setup;

            $installer->startSetup();
            $trigger = $this->triggerFactory->create()
                ->setName('trg_magento_pricerule_before_delete')
                ->setTime(\Magento\Framework\DB\Ddl\Trigger::TIME_BEFORE)
                ->setEvent('DELETE')
                ->setTable($setup->getTable('catalog_product_entity_decimal'));

            $trigger->addStatement("IF (OLD.attribute_id=77 AND OLD.store_id!=0)THEN signal SQLSTATE '23000' SET message_text = 'Cannot delete this record, inheriting the price of a product is not allowed'; END IF;");

              $setup->getConnection()->dropTrigger($trigger->getName());
              $setup->getConnection()->createTrigger($trigger);

          $installer->endSetup();
        } 
    }