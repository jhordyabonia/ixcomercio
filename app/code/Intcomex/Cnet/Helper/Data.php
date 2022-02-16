<?php
declare(strict_types=1);

namespace Intcomex\Cnet\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @const Cnet Configuration Data.
     */
	const CNET_GENERAL_ENABLED = 'cnet/general/enabled';
	const CNET_GENERAL_SKEY = 'cnet/general/skey';
    const CNET_GENERAL_ZONE_ID = 'cnet/general/zone_id';
    const CNET_GENERAL_MANUFACTURER = 'cnet/general/manufacturer';
    const GENERAL_COUNTRY_DEFAULT = 'general/country/default';
    const GENERAL_LOCALE_CODE = 'general/locale/code';

    /**
     * @param null $store
     * @return bool
     */
    public function isEnabled($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CNET_GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getSkey($store = null)
    {
        return $this->scopeConfig->getValue(
            self::CNET_GENERAL_SKEY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getZoneId($store = null)
    {
        return $this->scopeConfig->getValue(
            self::CNET_GENERAL_ZONE_ID,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getManufacturer($store = null)
    {
        return $this->scopeConfig->getValue(
            self::CNET_GENERAL_MANUFACTURER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getMarket($store = null)
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_COUNTRY_DEFAULT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getLang($store = null)
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_LOCALE_CODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
