<?php
/**
 * Grid GridInterface.
 * @category  Trax
 * @package   Trax_Grid
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2017 Trax Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Trax\Grid\Api\Data;

interface GridInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const CARRIER = 'carrier';
    const SERVICE_TYPE = 'service_type';
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
    * Get Carrier.
    *
    * @return varchar
    */
    public function getCarrier();

   /**
    * Set Carrier.
    */
    public function setCarrier($carrier);

   /**
    * Get ServiceType.
    *
    * @return varchar
    */
    public function getServiceType();

   /**
    * Set ServiceType.
    */
    public function setServiceType($service_type);

   /**
    * Get Publish Date.
    *
    * @return varchar
    */
    public function getTraxCode();

   /**
    * Set TraxCode.
    */
    public function setTraxCode($tTraxCode);

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
