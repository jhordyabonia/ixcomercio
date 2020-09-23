<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Data;

use Magento\Framework\Exception\LocalizedException;
use MagePal\EnhancedEcommerce\Block\CatalogLayer;
use MagePal\GoogleTagManager\Model\DataLayerEvent;

/**
 * Block : Category for catalog category view page
 *
 * @package MagePal\EnhancedEcommerce
 * @class   Category
 */
class Category extends CatalogLayer
{

    /**
     * Add category data to datalayer
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _dataLayer()
    {
        if ($list = $this->_eeHelper->getCategoryListType()) {
            $this->setListType($list);
        }

        $collection = $this->_getProducts();

        if (is_object($collection) && $collection->count()) {
            $this->setImpressionList(
                $this->getListType(),
                $this->_eeHelper->getCategoryListClassName(),
                $this->_eeHelper->getCategoryListContainerClass()
            );

            $products = $this->getProductImpressions($collection);

            $impressionsData = [
                'event' => DataLayerEvent::PRODUCT_IMPRESSION_EVENT,
                'ecommerce' => [
                    'impressions' => $products,
                    'currencyCode' => $this->getStoreCurrencyCode()
                ]
            ];

            $this->addCustomDataLayerByEvent(DataLayerEvent::PRODUCT_IMPRESSION_EVENT, $impressionsData);
        }

        return $this;
    }
}
