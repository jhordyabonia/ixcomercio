<?php
 
namespace Intcomex\Credomatic\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class Credomatic extends AbstractDb
{
    public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('transacciones_credomatic', 'order_id'); 
    }
}