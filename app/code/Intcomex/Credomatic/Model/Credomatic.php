<?php
 
namespace Intcomex\Credomatic\Model;
 
 
class Credomatic extends  \Magento\Framework\Model\AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Intcomex\Credomatic\Model\ResourceModel\Credomatic');
    }
}