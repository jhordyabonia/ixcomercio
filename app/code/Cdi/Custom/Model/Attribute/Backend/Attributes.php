<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Cdi\Custom\Model\Attribute\Backend;

class Attributes extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend{
    /**
     * Validate
     * @param \Magento\Catalog\Model\Product || \Magento\Catalog\Model\Cateogry $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function validate($object){
        /*
		$value = $object->getData($this->getAttribute()->getAttributeCode());
        if($value == 'wool'){
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Bottom can not be wool.')
            );
        }
		*/
        return true;
    }
}