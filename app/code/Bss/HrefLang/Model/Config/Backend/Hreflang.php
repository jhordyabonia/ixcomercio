<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_HrefLang
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\HrefLang\Model\Config\Backend;

/**
 * Class Hreflang
 *
 * @package Bss\HrefLang\Model\Config\Backend
 */
class Hreflang extends \Magento\Config\Model\Config\Backend\Serialized
{
    /**
     * @inheritDoc
     *
     * @return \Magento\Config\Model\Config\Backend\Serialized $this
     * @throws \Exception
     * @SuppressWarnings(CyclomaticComplexity)
     */
    public function beforeSave()
    {
        /* @var array $value */
        $value = $this->getValue();

        // If disable Hreflang config
        // Still save Hreflang value as old value instead of null
        // So, after admin change to yes
        // Data is remain
        $hrefLangData = $this->getData('fieldset_data');
        if (isset($hrefLangData['active']) && $hrefLangData['active'] == 0) {
            $value = json_decode($this->getOldValue(), true);
        }
        // End
        
        $valueCheck = [];
        // $countValue = array_count_values($value);
        foreach ($value as $key => $valueForCheck) {
            if (is_array($valueForCheck)) {
                $valueCheck[] = $valueForCheck['store'];
            }
            if (!isset($valueForCheck['language']) || !isset($valueForCheck['country'])) {
                continue;
            }
            if ('x-default' === $valueForCheck['language']
                && 'not_assign' !== $valueForCheck['country']
            ) {
                throw new \Exception(__('Add HREFLANG for Store View'));
            }
        }
        $checkCounts = array_count_values($valueCheck);
        foreach ($checkCounts as $key => $checkCount) {
            $checkCountInt = (int)$checkCount;
            if ($checkCountInt > 1) {
                throw new \Exception(__('Duplicate Store View'));
            }
        }
        if (is_array($value)) {
            unset($value['__empty']);
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}
