<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            %!uniqueid!%
 * Last Modified: 2018-08-30T11:54:31+00:00
 * File:          Block/Adminhtml/Profile/Edit/DefaultTemplatePopup.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Profile\Edit;

use Magento\Backend\Block\Template;

class DefaultTemplatePopup extends Template
{
    public function isDemoEnvironment()
    {
        return false;
    }
}
