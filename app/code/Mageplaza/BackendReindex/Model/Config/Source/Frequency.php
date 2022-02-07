<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mageplaza\BackendReindex\Model\Config\Source;


class Frequency implements \Magento\Framework\Option\ArrayInterface
{
   
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $backupTypes = [];
        $backupTypes[] = ['label' => 'Hourly', 'value' => 'H'];
        $backupTypes[] = ['label' => 'Daily', 'value' => 'D'];
        $backupTypes[] = ['label' => 'Weekly', 'value' => 'W'];
        $backupTypes[] = ['label' => 'Monthly', 'value' => 'M'];
        return $backupTypes;
    }
}
