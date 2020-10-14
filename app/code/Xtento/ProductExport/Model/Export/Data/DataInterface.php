<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          Model/Export/Data/DataInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Data;

interface DataInterface
{
    public function getExportData($entityType, $collectionItem);
    public function getConfiguration();
}
