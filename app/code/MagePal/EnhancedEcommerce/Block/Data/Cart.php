<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Data;

use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use MagePal\EnhancedEcommerce\Block\CatalogLayer;
use MagePal\GoogleTagManager\DataLayer\ProductData\ProductImpressionProvider;
use MagePal\EnhancedEcommerce\Helper\Data;
use MagePal\EnhancedEcommerce\Model\Session as EnhancedEcommerceSession;

use MagePal\GoogleTagManager\Helper\Data as GtmHelper;
use MagePal\GoogleTagManager\Model\DataLayerEvent;

/**
 * Class Cart
 * @package MagePal\EnhancedEcommerce\Block\Data
 */
class Cart extends CatalogLayer
{
    /**
     * Cart constructor.
     * @param Context $context
     * @param Resolver $layerResolver
     * @param Registry $registry
     * @param CatalogData $catalogHelper
     * @param EnhancedEcommerceSession $enhancedEcommerceSession
     * @param Data $eeHelper
     * @param GtmHelper $gtmHelper
     * @param ProductImpressionProvider $productImpressionProvider
     * @param array $data
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        Resolver $layerResolver,
        Registry $registry,
        CatalogData $catalogHelper,
        EnhancedEcommerceSession $enhancedEcommerceSession,
        Data $eeHelper,
        GtmHelper $gtmHelper,
        ProductImpressionProvider $productImpressionProvider,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $layerResolver,
            $registry,
            $catalogHelper,
            $gtmHelper,
            $eeHelper,
            $productImpressionProvider,
            $data
        );

        $this->enhancedEcommerceSession = $enhancedEcommerceSession;
    }

    /**
     * @var EnhancedEcommerce
     */
    protected $enhancedEcommerceSession;

    /**
     * Add category data to datalayer
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _dataLayer()
    {
        $list = $this->getCrossSellProduct();

        if ($list && count($list)) {
            $this->setImpressionList(
                $this->_eeHelper->getCrosssellListType(),
                $this->_eeHelper->getCrosssellClassName(),
                $this->_eeHelper->getCrosssellContainerClass()
            );

            $impressionsData = [
                'event' => DataLayerEvent::PRODUCT_IMPRESSION_EVENT,
                'ecommerce' => [
                    'impressions' => $list,
                    'currencyCode' => $this->getStoreCurrencyCode()
                ]
            ];

            $this->addCustomDataLayerByEvent(DataLayerEvent::PRODUCT_IMPRESSION_EVENT, $impressionsData);
        }

        $pushObject = $this->enhancedEcommerceSession->getProductDataObjectArray();

        if (!empty($pushObject) && is_array($pushObject)) {
            foreach ($pushObject as $object) {
                if (array_key_exists('ecommerce', $object)) {
                    if (array_key_exists('add', $object['ecommerce'])) {
                        $action = [
                            'cart' => [
                                'add' => $object['ecommerce']['add']
                            ]
                        ];
                        $this->addCustomDataLayer($action);
                    } elseif (array_key_exists('remove', $object['ecommerce'])) {
                        $action = [
                            'cart' => [
                                'remove' => $object['ecommerce']['remove']
                            ]
                        ];
                        $this->addCustomDataLayer($action);
                    }
                }

                $this->addCustomDataLayer($object);
            }
        }

        return $this;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function getCrossSellProduct()
    {
        $this->setBlockName('checkout.cart.crosssell');
        $this->setListType($this->_eeHelper->getCrosssellListType());
        return $this->getProductImpressions($this->_getProducts(true));
    }
}
