<?php

namespace Intcomex\Auditoria\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validator\Exception;
use Magento\Store\Model\ScopeInterface;
use Intcomex\Auditoria\Helper\ReferencePriceValidation;
use Magento\Store\Model\StoreManagerInterface;

class ObserverBeforeSaveProduct implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ReferencePriceValidation
     */
    private $priceValidation;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ReferencePriceValidation $priceValidation
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ReferencePriceValidation $priceValidation
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->priceValidation = $priceValidation;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute(Observer $observer): ObserverBeforeSaveProduct
    {
        /** @var Product $product */
        $product = $observer->getData('product');
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $storeId = $this->storeManager->getStore()->getId();

        if ((int)$product->getStatus() === Status::STATUS_ENABLED) {
            $result = $this->priceValidation->execute($product, $product->getPrice(), $product->getSpecialPrice(), $websiteCode, $storeId);
            if ($result !== true) {
                $this->priceValidation->sendReferencePriceErrorEmail($result['errors'], $result['website'], $result['store']);
                $message = $this->scopeConfig->getValue('auditoria/price/message', ScopeInterface::SCOPE_STORE);
                throw new Exception(__($message));
            }
        }

        return $this;
    }
}
