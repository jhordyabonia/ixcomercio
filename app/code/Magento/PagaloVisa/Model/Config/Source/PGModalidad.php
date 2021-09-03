<?php                                                                 
 namespace Magento\PagaloVisa\Model\Config\Source;                         
 class PGModalidad implements \Magento\Framework\Option\ArrayInterface            
 {
/**
 * Options for Type
 *
 * @return array
 */
public function toOptionArray()
{
    return [
        ['value' =>  'CyberSource', 'label' => __('CyberSource')],
        ['value' =>  'EPAY', 'label' => __('EPAY')]
    ];
}}
