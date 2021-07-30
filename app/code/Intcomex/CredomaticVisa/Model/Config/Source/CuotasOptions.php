<?php                                                                 
 namespace Intcomex\CredomaticVisa\Model\Config\Source;                         
 class CuotasOptions implements \Magento\Framework\Option\ArrayInterface            
 {
/**
 * Options for Type
 *
 * @return array
 */
public function toOptionArray()
{
    return [
        ['value' =>  3, 'label' => __('TASA0 3')],
        ['value' =>  6, 'label' => __('TASA0 6')],
    ];
}}