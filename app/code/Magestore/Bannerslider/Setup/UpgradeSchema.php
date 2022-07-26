<?php
namespace Magestore\Bannerslider\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface{
 
	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context){
		$installer = $setup;
		$installer->startSetup();
		
		if(version_compare($context->getVersion(), '1.7.3', '<')) {
			
			$table = $installer->getTable('magestore_bannerslider_banner');
			$columns = array(
				'title' => array(
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'Frontend banner title',
				),
				'banner_type' => array(
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'Tipo de banner',
				),
				'font_color' => array(
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'Color de la fuente',
				),
				'text_location' => array(
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'Ubicación del texto',
				),
			);
			$connection = $installer->getConnection();
			foreach ($columns as $name => $definition){
				$connection->addColumn($table, $name, $definition);
			}
			$installer->endSetup();
		}
		
		if(version_compare($context->getVersion(), '1.7.4', '<')) {
			
			$table = $installer->getTable('magestore_bannerslider_banner');
			$columns = array(
				'banner_class' => array(
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'Banner classes',
				),
				'banner_css' => array(
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'Banner custom css',
				),
			);
			$connection = $installer->getConnection();
			foreach ($columns as $name => $definition){
				$connection->addColumn($table, $name, $definition);
			}
			$installer->endSetup();
		}
		
		
		if(version_compare($context->getVersion(), '1.7.5', '<')) {
			
			$table = $installer->getTable('magestore_bannerslider_banner');
			$columns = array(
				'imageresp' => array(
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'Responsive banner image'
				),
			);
			$connection = $installer->getConnection();
			foreach ($columns as $name => $definition){
				$connection->addColumn($table, $name, $definition);
			}
			$installer->endSetup();
		}
		
		if(version_compare($context->getVersion(), '1.7.6', '<')) {
			
			$table = $installer->getTable('magestore_bannerslider_banner');
			$columns = array(
				'buttontext' => array(
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'Button text'
				),
			);
			$connection = $installer->getConnection();
			foreach ($columns as $name => $definition){
				$connection->addColumn($table, $name, $definition);
			}
			$installer->endSetup();
		}
		
		if(version_compare($context->getVersion(), '1.7.7', '<')) {
			
			$table = $installer->getTable('magestore_bannerslider_banner');
			$columns = array(
				'text_location_v' => array(
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'Ubicacion vertical del texto',
				),
			);
			$connection = $installer->getConnection();
			foreach ($columns as $name => $definition){
				$connection->addColumn($table, $name, $definition);
			}
			$installer->endSetup();
		}
	}
}