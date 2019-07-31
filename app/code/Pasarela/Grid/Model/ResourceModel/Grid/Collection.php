<?php

/**
 * Grid Grid Collection.
 *
 * @category  Pasarela
 * @package   Pasarela_Grid
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2017 Pasarela Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Pasarela\Grid\Model\ResourceModel\Grid;

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
            'Pasarela\Grid\Model\Grid',
            'Pasarela\Grid\Model\ResourceModel\Grid'
        );
    }
}
