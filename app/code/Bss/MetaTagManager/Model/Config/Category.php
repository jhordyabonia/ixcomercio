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
 * @package    Bss_MetaTagManager
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MetaTagManager\Model\Config;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class Category extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollection;

    /**
     * @var \Bss\MetaTagManager\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Category constructor.
     * @param StoreManagerInterface $storeManager
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param \Bss\MetaTagManager\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryCollectionFactory $categoryCollectionFactory,
        \Bss\MetaTagManager\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->categoryCollection = $categoryCollectionFactory;
        $this->helper = $helper;
        $this->request = $request;
        parent::__construct($data);
    }

    /**
     * Convert array to option
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray()
    {
        $iStoreId = $this->request->getParam('store', '0');
        $cateCollection = $this->categoryCollection->create()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active')
            ->addAttributeToSelect('parent_id')
            ->setStoreId($iStoreId)
            ->addFieldToFilter('parent_id', ['gt' => 0])
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('level', ['gteq' => 1])
            ->addAttributeToSort('path', 'asc');
        $result = [];
        foreach ($cateCollection as $oCategory) {
            $categoryName = $oCategory->getName();
            $sLabel = $categoryName."(ID: ".$oCategory->getId().")";
            $iPadWidth = ($oCategory->getLevel() - 1) * 2 + strlen($sLabel);
            $sLabel = str_pad($sLabel, $iPadWidth, '---', STR_PAD_LEFT);

            $result[] = [
                'label' => $sLabel,
                'value' => $oCategory->getId()
            ];
        }

        return $result;
    }
}
