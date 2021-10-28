<?php

namespace Intcomex\GridImages\Model\Adminhtml\Config\Settings\Product;
 

 
class GalleryGrid implements \Magento\Framework\Option\ArrayInterface
{
    
 
    public function __construct(
        
    ) {
        
    }
 
    public function getOptionArray()
    {
        
        $options = [];

        $options["default"] = "Default";
        $options["grid"] = "Grid Image";
        
        return $options;
        
        
    }
 
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }
 
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }
 
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}