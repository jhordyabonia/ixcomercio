<?php
declare(strict_types=1);

namespace Intcomex\Managereindex\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Helper extends AbstractHelper
{
    /**
     * @const Bin campaign data.
     */
    const GENERAL_ENABLED = 'disable_indexer/general/enabled';
   
    /**
     * @param null $store
     * @return bool
     */
    public function isEnabled($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::GENERAL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
    
}
