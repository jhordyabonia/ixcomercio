<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Data;

/**
 * Block : Category for catalog category view page
 *
 * @package MagePal\EnhancedEcommerce
 * @class   Category
 */
class Home extends CatalogWidget
{
    public function addImpressionList()
    {
        $this->setImpressionList(
            $this->getListType(),
            $this->_eeHelper->getHomeWidgetClassName(),
            $this->_eeHelper->getHomeWidgetContainerClass()
        );
    }

    protected function _init()
    {
        $this->setListType($this->_eeHelper->getHomeWidgetListType());
        $this->getUseWidgetTitle($this->_eeHelper->getHomeWidgetUseWidgetTitle());
        return $this;
    }
}
