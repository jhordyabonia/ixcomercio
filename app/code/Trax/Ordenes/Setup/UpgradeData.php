<?php
/**
 *
 * @category  Trax
 * @package   Trax_Ordenes
 * @author    Trax
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Trax\Ordenes\Setup;

use Magento\Eav\Model\Config;
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
    private $_eavSetupFactory;

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
        $this->_eavSetupFactory     = $eavSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);		
		if(version_compare($context->getVersion(), '1.0.2', '<')){
            $eavSetup->addAttribute('customer_address', 'identificacion', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'Identificación',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'system'=> false,
                'group'=> 'General',
                'global' => true,
                'visible_on_front' => true,
            ]);	
       
            $customAttribute = $this->eavConfig->getAttribute('customer_address', 'identificacion');
    
            $customAttribute->setData(
                'used_in_forms',
                ['adminhtml_customer_address','customer_address_edit','customer_register_address']
            );
            $customAttribute->save();
		}
        $setup->endSetup();
    }
}