<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Data;

use Cdi\Custom\Helper\Data as CdiData;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use MagePal\EnhancedEcommerce\Block\CatalogLayer;
use MagePal\GoogleTagManager\DataLayer\ProductData\ProductImpressionProvider;
use MagePal\EnhancedEcommerce\Helper\Data;
use MagePal\EnhancedEcommerce\Helper\Esp;
use MagePal\GoogleTagManager\Helper\Data as GtmHelper;
use MagePal\GoogleTagManager\Model\DataLayerEvent;

/**
 * Enhanced Success Page Extension
 */
class EnhancedSuccessPage extends CatalogLayer
{
    /** @var Esp */
    protected $espHelper;

    /**
     * CatalogLayer constructor.
     * @param Context $context
     * @param Resolver $layerResolver
     * @param Registry $registry
     * @param CatalogData $catalogHelper
     * @param GtmHelper $gtmHelper
     * @param Data $eeHelper
     * @param ProductImpressionProvider $productImpressionProvider
     * @param Esp $espHelper
     * @param CdiData $cdiData
     * @param array $data
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        Resolver $layerResolver,
        Registry $registry,
        CatalogData $catalogHelper,
        GtmHelper $gtmHelper,
        Data $eeHelper,
        ProductImpressionProvider $productImpressionProvider,
        Esp $espHelper,
        CdiData $cdiData,
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
            $cdiData,
            $data
        );
        $this->espHelper = $espHelper;
    }

    /**
     * Add category data to datalayer
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _dataLayer()
    {
        $relatedProduct = $this->getRelatedProduct();
        $upsellProduct = $this->getUpsellProduct();
        $crossSellProduct = $this->getCrossSellProduct();
        $recentlyViewedProduct = $this->geRecentlyViewProduct();

        $list = array_merge($relatedProduct, $upsellProduct, $crossSellProduct, $recentlyViewedProduct);

        if (!empty($list)) {
            $impressionsListData = [
                'environment' => DataLayerEvent::ENVIRONMENT,
                'event' => DataLayerEvent::PRODUCT_IMPRESSION_EVENT,
                'currencyCode' => $this->getStoreCurrencyCode(),
                'ecommerce' => [
                    'impressions' => $list
                ]
            ];

            $this->addCustomDataLayerByEvent(DataLayerEvent::PRODUCT_IMPRESSION_EVENT, $impressionsListData);

            if (!empty($relatedProduct)) {
                $this->setImpressionList(
                    $this->espHelper->getRelatedListType(),
                    $this->espHelper->getRelatedClassName(),
                    $this->espHelper->getRelatedContainerClass()
                );
            }

            if (!empty($upsellProduct)) {
                $this->setImpressionList(
                    $this->espHelper->getUpsellListType(),
                    $this->espHelper->getUpsellClassName(),
                    $this->espHelper->getUpsellContainerClass()
                );
            }

            if (!empty($crossSellProduct)) {
                $this->setImpressionList(
                    $this->espHelper->getCrosssellListType(),
                    $this->espHelper->getCrosssellClassName(),
                    $this->espHelper->getCrosssellContainerClass()
                );
            }

            if (!empty($recentlyViewedProduct)) {
                $this->setImpressionList(
                    $this->espHelper->getRecentViewedListType(),
                    $this->espHelper->getRecentViewedClassName(),
                    $this->espHelper->getRecentViewedContainerClass()
                );
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function isRelatedOrCrosssell()
    {
        return true;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getRelatedProduct()
    {
        $this->setBlockName('product.related');
        $this->setListType($this->espHelper->getRelatedListType());
        return $this->getProductImpressions($this->_getProductCollection(true));
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getUpsellProduct()
    {
        $this->setBlockName('product.upsell');
        $this->setListType($this->espHelper->getUpsellListType());
        return $this->getProductImpressions($this->_getProductCollection(true));
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getCrossSellProduct()
    {
        $this->setBlockName('product.crosssell');
        $this->setListType($this->espHelper->getCrosssellListType());
        return $this->getProductImpressions($this->_getProductCollection(true));
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function geRecentlyViewProduct()
    {
        $this->setBlockName('product.recently.view');
        $this->setListType($this->espHelper->getRecentViewedListType());
        return $this->getProductImpressions($this->_getProductCollection(true));
    }
}
