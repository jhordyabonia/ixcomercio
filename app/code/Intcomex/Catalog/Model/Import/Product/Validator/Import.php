<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Intcomex\Catalog\Model\Import\Product\Validator;

use \Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

class Import extends \Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var \Intcomex\Catalog\Helper\Data
     */
    protected $helper_data;

    /**
     * @var Product\StoreResolver
     */
    protected $storeResolver;

    protected $products = array();

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param RowValidatorInterface[] $validators
     */
    public function __construct(
        \Intcomex\Catalog\Helper\Data $helper_data,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
    ) {
        $this->helper_data = $helper_data;
        $this->storeResolver = $storeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        array_push($this->products,$value['sku']);
        $this->_clearMessages();
        if(count($this->products) > count(array_unique($this->products))){
            $this->_addMessages(['Duplicate products error.']);
            return false;
        }
        return true;
    }
}