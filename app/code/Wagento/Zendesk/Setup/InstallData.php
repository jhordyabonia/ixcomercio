<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Setup;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    const ZD_USER_ID = 'zd_user_id';

    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    private $eavSetup;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * InstallData constructor.
     * @param \Magento\Eav\Setup\EavSetup $eavSetup
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetup $eavSetup,
        \Magento\Eav\Model\Config $eavConfig
    ) {
    
        $this->eavSetup = $eavSetup;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $config = [
            'label' => 'Zendesk User Id',
            'type' => 'varchar',
            'input' => 'text',
            'visible' => true,
            'required' => false,
            'position' => 150,
            'sort_order' => 150,
            'system' => false
        ];

        /** CUSTOMER ATTRIBUTE */
        $this->eavSetup->addAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            self::ZD_USER_ID,
            $config
        );
        $customerAttribute = $this->eavConfig->getAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            self::ZD_USER_ID
        );
        $customerAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer']
        )->save();

        $setup->endSetup();
    }
}
