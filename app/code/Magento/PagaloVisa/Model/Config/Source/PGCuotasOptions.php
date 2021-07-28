<?php                                                                 
 namespace Magento\PagaloVisa\Model\Config\Source;                         
 class PGCuotasOptions implements \Magento\Framework\Option\ArrayInterface            
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
        ['value' =>  2, 'label' => __('2 Cuotas')],
        ['value' =>  3, 'label' => __('3 Cuotas')],
        ['value' =>  6, 'label' => __('6 Cuotas')],
        ['value' =>  10, 'label' => __('10 Cuotas')],
        ['value' =>  12, 'label' => __('12 Cuotas')]
    ];
}}