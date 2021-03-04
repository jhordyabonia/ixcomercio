<?php
namespace Intcomex\Credomatic\Model\Config\Source;                         

class Modo implements \Magento\Framework\Data\OptionSourceInterface
{
 public function toOptionArray()
 {
  return [
    ['value' => 'produccion', 'label' => __('Producción')],
    ['value' => 'pruebas', 'label' => __('Pruebas')],
  ];
 }
}