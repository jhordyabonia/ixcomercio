<?php                                                                 
 namespace Intcomex\CredomaticMasterCard\Model\Config\Source;                         
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
        ['value' =>  1, 'label' => __('Al Contado')],
        ['value' =>  3, 'label' => __('TASA0 3')],
        ['value' =>  6, 'label' => __('TASA0 6')],
    ];
}}