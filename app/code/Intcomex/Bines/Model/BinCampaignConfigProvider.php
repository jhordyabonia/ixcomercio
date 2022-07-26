<?php
declare(strict_types=1);

namespace Intcomex\Bines\Model;

use Intcomex\Bines\Helper\BinCampaign;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class BinCampaignConfigProvider implements ConfigProviderInterface
{
    /**
     * @var BinCampaign
     */
    protected $binCampaignHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param BinCampaign $binCampaignHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        BinCampaign $binCampaignHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->binCampaignHelper = $binCampaignHelper;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        $storeId = $this->storeManager->getStore()->getId();
        return [
            'binCampaign' => [
                'enabled'       => $this->binCampaignHelper->isEnabled($storeId),
                'ids'           => $this->binCampaignHelper->getIds($storeId),
                'binCodeLength' => $this->binCampaignHelper->getBinCodeLength($storeId) * 1
            ]
        ];
    }
}
