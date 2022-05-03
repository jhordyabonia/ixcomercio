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
        ['value' =>  1, 'label' => __('Contado')],
        ['value' =>  3, 'label' => __('3 Cuotas')],
        ['value' =>  6, 'label' => __('6 Cuotas')],
        ['value' =>  10, 'label' => __('10 Cuotas')],
        ['value' =>  12, 'label' => __('12 Cuotas')],
    ];
}}