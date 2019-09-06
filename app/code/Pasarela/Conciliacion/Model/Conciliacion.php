<?php

/**
 * Conciliacion Conciliacion Model.
 * @category  Pasarela
 * @package   Pasarela_Conciliacion
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2017 Pasarela Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Pasarela\Conciliacion\Model;

use Pasarela\Conciliacion\Api\Data\ConciliacionInterface;

class Conciliacion extends \Magento\Framework\Model\AbstractModel implements ConciliacionInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'bancomer_conciliation';

    /**
     * @var string
     */
    protected $_cacheTag = 'bancomer_conciliation';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'bancomer_conciliation';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Pasarela\Conciliacion\Model\ResourceModel\Conciliacion');
    }
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get ConciliationDate.
     *
     * @return varchar
     */
    public function getConciliationDate()
    {
        return $this->getData(self::CONCILIATION_DATE);
    }

    /**
     * Set ConciliationDate.
     */
    public function setConciliationDate($conciliationDate)
    {
        return $this->setData(self::CONCILIATION_DATE, $conciliationDate);
    }

    /**
     * Get getProcesedPayments.
     *
     * @return varchar
     */
    public function getProcesedPayments()
    {
        return $this->getData(self::PROCESED_PAYMENTS);
    }

    /**
     * Set ProcesedPayments.
     */
    public function setProcesedPayments($procesedPayments)
    {
        return $this->setData(self::PROCESED_PAYMENTS, $procesedPayments);
    }

    /**
     * Get getProcesedOrders.
     *
     * @return varchar
     */
    public function getProcesedOrders()
    {
        return $this->getData(self::PROCESED_ORDERS);
    }

    /**
     * Set ProcesedOrders.
     */
    public function setProcesedOrders($procesedOrders)
    {
        return $this->setData(self::PROCESED_ORDERS, $procesedOrders);
    }

    /**
     * Get getUnprocesedPayments.
     *
     * @return varchar
     */
    public function getUnprocesedPayments()
    {
        return $this->getData(self::UNPROCESED_ORDERS);
    }

    /**
     * Set setUnprocesedPayments.
     */
    public function setUnprocesedPayments($unprocesedPayments)
    {
        return $this->setData(self::UNPROCESED_ORDERS, $unprocesedPayments);
    }

}
