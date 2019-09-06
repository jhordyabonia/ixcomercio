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
    const PAYMENT_TYPE = 'payment_type';
    const GATEWAY = 'gateway';
    const PAYMENT_CODE = 'payment_code';
    const TRAX_CODE = 'trax_code';
    const COUNTRY_CODE = 'country_code';
    const STORE_CODE = 'store_code';

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
    * Get PaymentType.
    *
    * @return varchar
    */
    public function getPaymentType();

   /**
    * Set PaymentType.
    */
    public function setPaymentType($paymentType);

   /**
    * Get Gateway.
    *
    * @return varchar
    */
    public function getGateway();

   /**
    * Set Gateway.
    */
    public function setGateway($gateway);

    /**
     * Get PaymentCode.
     *
     * @return varchar
     */
     public function getPaymentCode();
 
    /**
     * Set setPaymentCode.
     */
     public function setPaymentCode($paymentCode);

   /**
    * Get Publish Date.
    *
    * @return varchar
    */
    public function getTraxCode();

   /**
    * Set setTraxCode.
    */
    public function setTraxCode($traxCode);

   /**
    * Get CountryCode.
    *
    * @return varchar
    */
    public function getCountryCode();

   /**
    * Set StartingPrice.
    */
    public function setCountryCode($countryCode);

   /**
    * Get StoreCode.
    *
    * @return varchar
    */
    public function getStoreCode();

   /**
    * Set StoreCode.
    */
    public function setStoreCode($storeCode);
}
