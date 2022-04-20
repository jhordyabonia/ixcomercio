<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RichSnippets
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RichSnippets\Model\Config\Source;

/**
 * Class BusinessType
 * @package Bss\RichSnippets\Model\Config\Source
 */
class BusinessType extends \Magento\Framework\View\Element\Template
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $dataReturn = [
            ['value' => 'LocalBusiness', 'label' => 'LocalBusiness'],
            ['value' => 'AutoPartsStore', 'label' => 'AutoPartsStore'],
            ['value' => 'BikeStore', 'label' => 'BikeStore'],
            ['value' => 'BookStore', 'label' => 'BookStore'],
            ['value' => 'ClothingStore', 'label' => 'ClothingStore'],
            ['value' => 'ComputerStore', 'label' => 'ComputerStore'],
            ['value' => 'ConvenienceStore', 'label' => 'ConvenienceStore'],
            ['value' => 'DepartmentStore', 'label' => 'DepartmentStore'],
            ['value' => 'ElectronicsStore', 'label' => 'ElectronicsStore'],
            ['value' => 'Florist', 'label' => 'Florist'],
            ['value' => 'FurnitureStore', 'label' => 'FurnitureStore'],
            ['value' => 'GardenStore', 'label' => 'GardenStore'],
            ['value' => 'GroceryStore', 'label' => 'GroceryStore'],
            ['value' => 'HardwareStore', 'label' => 'HardwareStore'],
            ['value' => 'HobbyShop', 'label' => 'HobbyShop'],
            ['value' => 'HomeGoodsStore', 'label' => 'HomeGoodsStore'],
            ['value' => 'JewelryStore', 'label' => 'JewelryStore'],
            ['value' => 'LiquorStore', 'label' => 'LiquorStore'],
            ['value' => 'MensClothingStore', 'label' => 'MensClothingStore'],
            ['value' => 'MobilePhoneStore', 'label' => 'MobilePhoneStore'],
            ['value' => 'MovieRentalStore', 'label' => 'MovieRentalStore'],
            ['value' => 'MusicStore', 'label' => 'MusicStore'],
            ['value' => 'OfficeEquipmentStore', 'label' => 'OfficeEquipmentStore'],
            ['value' => 'OutletStore', 'label' => 'OutletStore'],
            ['value' => 'PawnShop', 'label' => 'PawnShop'],
            ['value' => 'PetStore', 'label' => 'PetStore'],
            ['value' => 'ShoeStore', 'label' => 'ShoeStore'],
            ['value' => 'SportingGoodsStore', 'label' => 'SportingGoodsStore'],
            ['value' => 'TireShop', 'label' => 'TireShop'],
            ['value' => 'ToyStore', 'label' => 'ToyStore'],
            ['value' => 'WholesaleStore', 'label' => 'WholesaleStore']
        ];
        return $dataReturn;
    }
}
