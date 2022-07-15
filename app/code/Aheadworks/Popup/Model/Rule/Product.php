<?php
/**
 * Aheadworks Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://ecommerce.aheadworks.com/end-user-license-agreement/
 *
 * @package    Popup
 * @version    1.2.3
 * @copyright  Copyright (c) 2020 Aheadworks Inc. (http://www.aheadworks.com)
 * @license    https://ecommerce.aheadworks.com/end-user-license-agreement/
 */
namespace Aheadworks\Popup\Model\Rule;

/**
 * Class Product
 * @package Aheadworks\Popup\Model\Rule
 */
class Product extends \Magento\Rule\Model\AbstractModel
{
    /**
     * Store matched product Ids
     *
     * @var array
     */
    private $productIds = null;

    /**
     * Combine factory
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    private $combineFactory;

    /**
     * Action collection factory
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    private $actionCollectionFactory;

    /**
     * Product factory
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * Product collection factory
     * @var \Aheadworks\Popup\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Condition\CombineFactory $combineFactory
     * @param \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory
     * @param \Aheadworks\Popup\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Aheadworks\Popup\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Aheadworks\Popup\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->combineFactory = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $localeDate, null, null, $data);
    }

    /**
     * Getter for rule conditions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine|\Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * Reset rule combine conditions
     *
     * @param null $conditions
     * @return $this
     */
    protected function _resetConditions($conditions = null)
    {
        parent::_resetConditions($conditions);
        $this->getConditions($conditions)
            ->setId('1')
            ->setPrefix('popup');
        return $this;
    }

    /**
     * Get validated product ids
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if ($this->productIds === null) {
            $this->productIds = [];
            $this->setCollectedAttributes([]);

            /** @var $productCollection \Aheadworks\Popup\Model\ResourceModel\Product\Collection */
            $productCollection = $this->productCollectionFactory->create();
            $this->getConditions()->collectValidatedAttributes($productCollection);
            $productIds = $productCollection->getAllIds();
            $this->productIds = array_unique($productIds);
        }
        return $this->productIds;
    }
}
