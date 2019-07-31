<?php
/**
 * Grid Grid ResourceModel.
 * @category  Trax
 * @package   Trax_Grid
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2016 Trax Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Trax\Grid\Model\ResourceModel;

/**
 * Grid Grid mysql resource.
 */
class Grid extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Construct.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime       $date
     * @param string|null                                       $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('trax_match_carrier', 'id');
    }
}
