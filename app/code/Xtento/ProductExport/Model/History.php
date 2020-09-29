<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          Model/History.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model;

/**
 * Class History
 * @package Xtento\ProductExport\Model
 */
class History extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Xtento\ProductExport\Model\ResourceModel\History');
        $this->_collectionName = 'Xtento\ProductExport\Model\ResourceModel\History\Collection';
    }
}
