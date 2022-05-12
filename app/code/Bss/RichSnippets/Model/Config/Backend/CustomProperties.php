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
 * @package    Bss_RichSnippets
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RichSnippets\Model\Config\Backend;

/**
 * Class CustomProperties
 * @package Bss\RichSnippets\Model\Config\Backend
 */
class CustomProperties extends \Magento\Config\Model\Config\Backend\Serialized
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
        /* @var array $value*/
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}
