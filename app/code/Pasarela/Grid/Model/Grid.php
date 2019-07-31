<?php

/**
 * Grid Grid Model.
 * @category  Pasarela
 * @package   Pasarela_Grid
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2017 Pasarela Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Pasarela\Grid\Model;

use Pasarela\Grid\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'trax_match_payment';

    /**
     * @var string
     */
    protected $_cacheTag = 'trax_match_payment';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'trax_match_payment';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Pasarela\Grid\Model\ResourceModel\Grid');
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
     * Get PaymentType.
     *
     * @return varchar
     */
    public function getPaymentType()
    {
        return $this->getData(self::PAYMENT_TYPE);
    }

    /**
     * Set PaymentType.
     */
    public function setPaymentType($paymentType)
    {
        return $this->setData(self::PAYMENT_TYPE, $paymentType);
    }

    /**
     * Get getGateway.
     *
     * @return varchar
     */
    public function getGateway()
    {
        return $this->getData(self::GATEWAY);
    }

    /**
     * Set Gateway.
     */
    public function setGateway($gateway)
    {
        return $this->setData(self::GATEWAY, $gateway);
    }

    /**
     * Get getPaymentCode.
     *
     * @return varchar
     */
    public function getPaymentCode()
    {
        return $this->getData(self::GATEWAY);
    }

    /**
     * Set PaymentCode.
     */
    public function setPaymentCode($paymentCode)
    {
        return $this->setData(self::PAYMENT_CODE, $paymentCode);
    }

    /**
     * Get getTraxCode.
     *
     * @return varchar
     */
    public function getTraxCode()
    {
        return $this->getData(self::TRAX_CODE);
    }

    /**
     * Set setTraxCode.
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
