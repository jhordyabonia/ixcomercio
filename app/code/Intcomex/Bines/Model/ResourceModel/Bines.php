<?php
declare(strict_types=1);

namespace Intcomex\Bines\Model\ResourceModel;

class Bines extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('intcomex_bines', 'entity_id');
    }
}
