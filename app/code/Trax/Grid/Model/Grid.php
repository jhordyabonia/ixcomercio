<?php

/**
 * Grid Grid Model.
 * @category  Trax
 * @package   Trax_Grid
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2017 Trax Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Trax\Grid\Model;

use Trax\Grid\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'trax_match_carrier';

    /**
     * @var string
     */
    protected $_cacheTag = 'trax_match_carrier';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'trax_match_carrier';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Trax\Grid\Model\ResourceModel\Grid');
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
     * Get Carrier.
     *
     * @return varchar
     */
    public function getCarrier()
    {
        return $this->getData(self::CARRIER);
    }

    /**
     * Set Carrier.
     */
    public function setCarrier($carrier)
    {
        return $this->setData(self::CARRIER, $carrier);
    }

    /**
     * Get getServiceType.
     *
     * @return varchar
     */
    public function getServiceType()
    {
        return $this->getData(self::SERVICE_TYPE);
    }

    /**
     * Set ServiceType.
     */
    public function setServiceType($serviceType)
    {
        return $this->setData(self::SERVICE_TYPE, $serviceType);
    }

    /**
     * Get TraxCode.
     *
     * @return varchar
     */
    public function getTraxCode()
    {
        return $this->getData(self::TRAX_CODE);
    }

    /**
     * Set TraxCode.
     */
    public function setTraxCode($traxCode)
    {
        return $this->setData(self::TRAX_CODE, $traxCode);
    }

    /**
     * Get CountryCode.
     *
     * @return varchar
     */
    public function getCountryCode()
    {
        return $this->getData(self::COUNTRY_CODE);
    }

    /**
     * Set CountryCode.
     */
    public function setCountryCode($countryCode)
    {
        return $this->setData(self::COUNTRY_CODE, $countryCode);
    }

    /**
     * Get StoreCode.
     *
     * @return varchar
     */
    public function getStoreCode()
    {
        return $this->getData(self::STORE_CODE);
    }

    /**
     * Set StoreCode.
     */
    public function setStoreCode($storeCode)
    {
        return $this->setData(self::STORE_CODE, $storeCode);
    }
}
