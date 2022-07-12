<?php
declare(strict_types=1);

namespace Intcomex\Crocs\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @const Crocs data.
     */
    const CROCS_GENERAL_ENABLED = 'crocs/general/enabled';
    const CROCS_GENERAL_PREFIX = 'crocs/general/prefix';
    const CROCS_GENERAL_SEPARATOR = 'crocs/general/separator';

    /**
     * @param null $store
     * @return bool
     */
    public function isEnabled($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CROCS_GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getPrefix($store = null)
    {
        return $this->scopeConfig->getValue(
            self::CROCS_GENERAL_PREFIX,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getSeparator($store = null)
    {
        return $this->scopeConfig->getValue(
            self::CROCS_GENERAL_SEPARATOR,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
