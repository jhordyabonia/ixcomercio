<?php
/**
 * Conciliacion ConciliacionInterface.
 * @category  Pasarela
 * @package   Pasarela_Conciliacion
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2017 Pasarela Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Pasarela\Conciliacion\Api\Data;

interface ConciliacionInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const CONCILIATION_DATE = 'conciliation_date';
    const PROCESED_PAYMENTS = 'procesed_payments';
    const PROCESED_ORDERS = 'procesed_orders';
    const UNPROCESED_ORDERS = 'unprocesed_orders';

   /**
    * Get Id.
    *
    * @return int
    */
    public function getId();

   /**
    * Set Id.
    */
    public function setId($id);

   /**
    * Get ConciliationDate.
    *
    * @return varchar
    */
    public function getConciliationDate();

   /**
    * Set ConciliationDate.
    */
    public function setConciliationDate($conciliationDate);

   /**
    * Get ProcesedPayments.
    *
    * @return varchar
    */
    public function getProcesedPayments();

   /**
    * Set ProcesedPayments.
    */
    public function setProcesedPayments($procesedPayments);

    /**
     * Get UnprocesedPayments.
     *
     * @return varchar
     */
     public function getUnprocesedPayments();
 
    /**
     * Set setUnprocesedPayments.
     */
     public function setUnprocesedPayments($unprocesedPayments);

   /**
    * Get Publish Date.
    *
    * @return varchar
    */
    public function getProcesedOrders();

   /**
    * Set setProcesedOrders.
    */
    public function setProcesedOrders($procesedOrders);
}
