<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Helper;

/** Enhanced Success Page Data Helper */

class Esp extends Data
{

    /**
     * @return string
     */
    public function getUpsellListType()
    {
        return $this->getConfigValue('enhanced_success_page/upsell/list_type');
    }

    /**
     * @return string
     */
    public function getUpsellClassName()
    {
        return $this->getConfigValue('enhanced_success_page/upsell/class_name');
    }

    /**
     * @return string
     */
    public function getUpsellContainerClass()
    {
        return $this->getConfigValue('enhanced_success_page/upsell/container_class');
    }

    /**
     * @return string
     */
    public function getRelatedListType()
    {
        return $this->getConfigValue('enhanced_success_page/related/list_type');
    }

    /**
     * @return string
     */
    public function getRelatedClassName()
    {
        return $this->getConfigValue('enhanced_success_page/related/class_name');
    }

    /**
     * @return string
     */
    public function getRelatedContainerClass()
    {
        return $this->getConfigValue('enhanced_success_page/related/container_class');
    }

    /**
     * @return string
     */
    public function getCrosssellListType()
    {
        return $this->getConfigValue('enhanced_success_page/crosssell/list_type');
    }

    /**
     * @return string
     */
    public function getCrosssellClassName()
    {
        return $this->getConfigValue('enhanced_success_page/crosssell/class_name');
    }

    /**
     * @return string
     */
    public function getCrosssellContainerClass()
    {
        return $this->getConfigValue('enhanced_success_page/crosssell/container_class');
    }

    /**
     * @return string
     */
    public function getRecentViewedListType()
    {
        return $this->getConfigValue('enhanced_success_page/recent_viewed/list_type');
    }

    /**
     * @return string
     */
    public function getRecentViewedClassName()
    {
        return $this->getConfigValue('enhanced_success_page/recent_viewed/class_name');
    }

    /**
     * @return string
     */
    public function getRecentViewedContainerClass()
    {
        return $this->getConfigValue('enhanced_success_page/recent_viewed/container_class');
    }
}
