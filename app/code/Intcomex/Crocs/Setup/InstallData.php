<?php

namespace Intcomex\Crocs\Setup;

use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Swatches\Model\Swatch;

class InstallData implements InstallDataInterface
{
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    private $attributeOptionInterfaceFactory;

    /**
     * @var ProductAttributeOptionManagementInterface
     */
    private $attributeOptionManagement;

    /**
     * @param AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory
     * @param ProductAttributeOptionManagementInterface $attributeOptionManagement
     * @param AttributeSetFactory $attributeSetFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param Config $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory,
        ProductAttributeOptionManagementInterface $attributeOptionManagement,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory,
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->attributeOptionInterfaceFactory = $attributeOptionInterfaceFactory;;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        // Create Attribute Set Crocs
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $data = [
            'attribute_set_name' => 'Crocs',
            'entity_type_id'     => $entityTypeId,
            'sort_order'         => 100,
        ];
        $attributeSet = $this->attributeSetFactory->create();
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        $attributeSet->setData($data);
        $attributeSet->validate();
        $attributeSet->initFromSkeleton($attributeSetId);
        $attributeSet->save();

        // Product Attribute Color
        $eavSetup->addAttribute(
            Product::ENTITY,
            'crocs_color',
            [
                Swatch::SWATCH_INPUT_TYPE_KEY => Swatch::SWATCH_INPUT_TYPE_VISUAL,
                'type' => 'int',
                'label' => 'Color',
                'input' => 'swatch_visual',
                'required' => true,
                'visible' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'used_in_product_listing' => true,
                'visible_on_front' => true,
                'user_defined' => true,
                'filterable' => 1,
                'searchable' => true,
                'filterable_in_search' => true,
                'used_for_promo_rules' => true,
                'is_html_allowed_on_front' => false,
                'used_for_sort_by' => true
            ]
        );

        // Product Attribute Gender
        $eavSetup->addAttribute(
            Product::ENTITY,
            'crocs_gender',
            [
                Swatch::SWATCH_INPUT_TYPE_KEY => Swatch::SWATCH_INPUT_TYPE_TEXT,
                'type' => 'int',
                'label' => 'Gender',
                'input' => 'swatch_text',
                'required' => true,
                'visible' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'used_in_product_listing' => true,
                'visible_on_front' => true,
                'user_defined' => true,
                'filterable' => 1,
                'searchable' => true,
                'filterable_in_search' => true,
                'used_for_promo_rules' => true,
                'is_html_allowed_on_front' => false,
                'used_for_sort_by' => true
            ]
        );

        // Product Attribute Size
        $eavSetup->addAttribute(
            Product::ENTITY,
            'crocs_size',
            [
                Swatch::SWATCH_INPUT_TYPE_KEY => Swatch::SWATCH_INPUT_TYPE_TEXT,
                'type' => 'int',
                'label' => 'Size',
                'input' => 'swatch_text',
                'required' => true,
                'visible' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'used_in_product_listing' => true,
                'visible_on_front' => true,
                'user_defined' => true,
                'filterable' => 1,
                'searchable' => true,
                'filterable_in_search' => true,
                'used_for_promo_rules' => true,
                'is_html_allowed_on_front' => false,
                'used_for_sort_by' => true
            ]
        );

        // Product Attribute Fit
        $eavSetup->addAttribute(
            Product::ENTITY,
            'crocs_fit',
            [
                'type' => 'int',
                'label' => 'Fit',
                'input' => 'select',
                'required' => false,
                'visible' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'visible_on_front' => true,
                'user_defined' => true,
                'filterable' => 1,
                'searchable' => true,
                'filterable_in_search' => true,
                'used_for_promo_rules' => true,
                'is_html_allowed_on_front' => false,
                'used_for_sort_by' => true
            ]
        );

        // Product Attribute Style
        $eavSetup->addAttribute(
            Product::ENTITY,
            'crocs_style',
            [
                'type' => 'int',
                'label' => 'Style',
                'input' => 'select',
                'required' => false,
                'visible' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'visible_on_front' => true,
                'user_defined' => true,
                'filterable' => 1,
                'searchable' => true,
                'filterable_in_search' => true,
                'used_for_promo_rules' => true,
                'is_html_allowed_on_front' => false,
                'used_for_sort_by' => true
            ]
        );

        // Product Attribute Sizes Match
        $eavSetup->addAttribute(
            Product::ENTITY,
            'crocs_sizes_match',
            [
                'type' => 'int',
                'label' => 'Sizes Match',
                'input' => 'select',
                'required' => false,
                'visible' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => false,
                'visible_on_front' => false,
                'user_defined' => true,
                'filterable' => false,
                'searchable' => false,
                'filterable_in_search' => false,
                'used_for_promo_rules' => false,
                'is_html_allowed_on_front' => false,
                'used_for_sort_by' => false
            ]
        );

        // Add Brand Crocs
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'marca');
        $option = $this->attributeOptionInterfaceFactory->create();
        $option->setLabel('Crocs');
        $this->attributeOptionManagement->add(
            $attribute->getId(),
            $option
        );

        $setup->endSetup();
    }
}
