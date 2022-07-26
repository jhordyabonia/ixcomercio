<?php
declare(strict_types=1);

namespace Intcomex\Bines\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class BinCampaign extends AbstractHelper
{
    /**
     * @const Bin campaign data.
     */
    const BIN_CAMPAIGN_GENERAL_ENABLED = 'bin_campaign/general/enabled';
    const BIN_CAMPAIGN_GENERAL_IDS = 'bin_campaign/general/ids';
    const BIN_CAMPAIGN_GENERAL_BIN_CODE_LENGTH = 'bin_campaign/general/bin_code_length';

    /**
     * @param null $store
     * @return bool
     */
    public function isEnabled($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::BIN_CAMPAIGN_GENERAL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getIds($store = null)
    {
        return $this->scopeConfig->getValue(
            self::BIN_CAMPAIGN_GENERAL_IDS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getBinCodeLength($store = null)
    {
        return $this->scopeConfig->getValue(
            self::BIN_CAMPAIGN_GENERAL_BIN_CODE_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
