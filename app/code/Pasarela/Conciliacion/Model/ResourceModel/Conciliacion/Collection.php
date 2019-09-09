<?php

/**
 * Conciliacion Conciliacion Collection.
 *
 * @category  Pasarela
 * @package   Pasarela_Conciliacion
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2017 Pasarela Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Pasarela\Conciliacion\Model\ResourceModel\Conciliacion;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            'Pasarela\Conciliacion\Model\Conciliacion',
            'Pasarela\Conciliacion\Model\ResourceModel\Conciliacion'
        );
    }
}
