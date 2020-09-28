<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \MagePal\GoogleTagManager\Helper\Data
{

    /**
     * Whether Tag Manager is ready to use
     *
     * @param int $store_id
     * @return bool
     */
    public function isEnabled($store_id = null)
    {
        return parent::isEnabled($store_id) && $this->isEnhancedEcommerceEnabled($store_id);
    }

    /**
     *
     * @param int $store_id
     * @return bool
     */
    public function isRefundEnabled($store_id = null)
    {
        return $this->isEnabled($store_id) && $this->isSetFlag('enhanced_ecommerce/refund', $store_id);
    }

    /**
     *
     * @param int $store_id
     * @return bool
     */
    public function isAdminOrderTrackingEnabled($store_id = null)
    {
        return $this->isEnabled($store_id)
            && $this->isSetFlag('enhanced_ecommerce/admin_order_tracking', $store_id);
    }

    /**
     * Whether Tag Manager is ready to use
     *
     * @param int $store_id
     * @return bool
     */
    public function isEnhancedEcommerceEnabled($store_id = null)
    {
        return $this->isSetFlag('enhanced_ecommerce/active', $store_id);
    }

    /**
     * @return string
     */
    public function getUpsellListType()
    {
        return $this->getConfigValue('upsell/list_type');
    }

    /**
     * @return string
     */
    public function getUpsellClassName()
    {
        return $this->getConfigValue('upsell/class_name');
    }

    /**
     * @return string
     */
    public function getUpsellContainerClass()
    {
        return $this->getConfigValue('upsell/container_class');
    }

    /**
     * @return string
     */
    public function getRelatedListType()
    {
        return $this->getConfigValue('related/list_type');
    }

    /**
     * @return string
     */
    public function getRelatedClassName()
    {
        return $this->getConfigValue('related/class_name');
    }

    public function getRelatedContainerClass()
    {
        return $this->getConfigValue('related/container_class');
    }

    /**
     * @return string
     */
    public function getCrosssellListType()
    {
        return $this->getConfigValue('crosssell/list_type');
    }

    /**
     * @return string
     */
    public function getCrosssellClassName()
    {
        return $this->getConfigValue('crosssell/class_name');
    }

    /**
     * @return string
     */
    public function getCrosssellContainerClass()
    {
        return $this->getConfigValue('crosssell/container_class');
    }

    /**
     * @return string
     */
    public function getCategoryListType()
    {
        return $this->getConfigValue('category_list/list_type');
    }

    /**
     * @return string
     */
    public function getCategoryListClassName()
    {
        return $this->getConfigValue('category_list/class_name');
    }

    /**
     * @return string
     */
    public function getCategoryListContainerClass()
    {
        return $this->getConfigValue('category_list/container_class');
    }

    /**
     * @return string
     */
    public function getSearchListType()
    {
        return $this->getConfigValue('search_list/list_type');
    }

    /**
     * @return string
     */
    public function getSearchListClassName()
    {
        return $this->getConfigValue('search_list/class_name');
    }

    /**
     * @return string
     */
    public function getSearchListContainerClass()
    {
        return $this->getConfigValue('search_list/container_class');
    }

    /**
     * @return string
     */
    public function getCategoryWidgetListType()
    {
        return $this->getConfigValue('category_widget/list_type');
    }

    /**
     * @return string
     */
    public function getCategoryWidgetClassName()
    {
        return $this->getConfigValue('category_widget/class_name');
    }

    /**
     * @return string
     */
    public function getCategoryWidgetContainerClass()
    {
        return $this->getConfigValue('category_widget/container_class');
    }

    /**
     * @return bool
     */
    public function getCategoryWidgetUseWidgetTitle()
    {
        return $this->isSetFlag('category_widget/use_widget_title');
    }

    /**
     * @return string
     */
    public function getHomeWidgetListType()
    {
        return $this->getConfigValue('homepage_widget/list_type');
    }

    /**
     * @return string
     */
    public function getHomeWidgetClassName()
    {
        return $this->getConfigValue('homepage_widget/class_name');
    }

    /**
     * @return string
     */
    public function getHomeWidgetContainerClass()
    {
        return $this->getConfigValue('homepage_widget/container_class');
    }

    /**
     * @return int
     */
    public function getCheckoutShippingIndex()
    {
        return (int) $this->getConfigValue('checkout/shipping_index');
    }

    /**
     * @return int
     */
    public function getCheckoutPaymentIndex()
    {
        return (int) $this->getConfigValue('checkout/payment_index');
    }

    /**
     * @return bool
     */
    public function getHomeWidgetUseWidgetTitle()
    {
        return $this->isSetFlag('homepage_widget/use_widget_title');
    }

    /**
     * Get system config
     *
     * @param String path
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigValue($path, $store_id = null)
    {
        $path = 'googletagmanager/' . $path;
        //return value from core config
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    /**
     * Get system config
     *
     * @param String path
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function isSetFlag($path, $store_id = null)
    {
        $path = 'googletagmanager/' . $path;
        //return value from core config
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }
}
