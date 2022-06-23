<?php
declare(strict_types=1);

namespace Intcomex\Crocs\Observer;

use Intcomex\Crocs\Helper\Data;
use Intcomex\Crocs\Model\ConfigurableProduct;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Zend\Log\Logger;
use Exception;

class DiscountStockToMultiSize implements ObserverInterface
{
    /**
     * @var ConfigurableProduct
     */
    private $configurableProduct;

    /**
     * @var Data
     */
    private $crocsHelper;

    /**
     * @var GetSourceItemsDataBySku
     */
    private $sourceDataBySku;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ConfigurableProduct $configurableProduct
     * @param Data $crocsHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ConfigurableProduct $configurableProduct,
        Data $crocsHelper,
        GetSourceItemsDataBySku $sourceDataBySku,
        StoreManagerInterface $storeManager
    ) {
        $this->configurableProduct = $configurableProduct;
        $this->crocsHelper = $crocsHelper;
        $this->sourceDataBySku = $sourceDataBySku;
        $this->storeManager = $storeManager;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/crocs.log');
        $this->logger = new Logger();
        $this->logger->addWriter($writer);
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getData('order');
        $storeId = (int)$order->getStoreId();
        try {
            if ($this->crocsHelper->isEnabled($storeId)) {
                foreach ($order->getAllItems() as $item) {
                    if ($item->getProduct()->getTypeId() !== Configurable::TYPE_CODE) {
                        $mpn = $item->getProduct()->getData('mpn');
                        $websiteCode = $this->_getWebsiteCodeByStoreId($storeId);
                        $sizes = $this->configurableProduct->getSizes($mpn, $storeId);
                        if (count($sizes) > 1 && $websiteCode) {
                            $separator = $this->crocsHelper->getSeparator($storeId);
                            $skuExploded = explode($separator, $item->getSku());
                            $this->logger->debug('DiscountStockToMultiSize:: IsMultiSize: ' . $item->getSku());
                            foreach ($sizes as $size) {
                                if (isset($skuExploded[2]) && $skuExploded[2] !== $size) {
                                    $skuCousin = $skuExploded[0] . $separator . $skuExploded[1] . $separator . $size;
                                    $currentQuantity = $this->_getCurrentQuantityBySku($skuCousin, $websiteCode);
                                    $quantityToSet = $currentQuantity - $item->getQtyOrdered();
                                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                    $sourceItem = $objectManager->create('Magento\InventoryApi\Api\Data\SourceItemInterface');
                                    $sourceItem->setSku($skuCousin);
                                    $sourceItem->setSourceCode($websiteCode);
                                    $sourceItem->setQuantity($quantityToSet);
                                    $sourceItem->setStatus($quantityToSet > 0 ? 1 : 0);
                                    $sourceItemSave = $objectManager->get('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
                                    $sourceItemSave->execute([$sourceItem]);
                                    $this->logger->debug("DiscountStockToMultiSize:: CousinSku: $skuCousin CurrentQuantity: $currentQuantity QuantitySet: $quantityToSet");
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->debug('Error Updating Cousin Quantity: ' . $e->getMessage());
        }
    }

    /**
     * Get Website code by store id.
     *
     * @param int $storeId
     * @return string|null
     */
    private function _getWebsiteCodeByStoreId(int $storeId): ?string
    {
        try {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
        } catch (Exception $e) {
            $this->logger->debug('Error Getting WebsiteCode: ' . $e->getMessage());
            $websiteCode = null;
        }
        return $websiteCode;
    }

    /**
     * Get current quantity by Website Code.
     *
     * @param $sku
     * @param $websiteCode
     * @return mixed|null
     */
    private function _getCurrentQuantityBySku($sku, $websiteCode)
    {
        $response = null;
        try {
            $quantities = $this->sourceDataBySku->execute($sku);
            foreach ($quantities as $quantity) {
                if ($quantity['source_code'] === $websiteCode) {
                    $response = $quantity['quantity'];
                }
            }
        } catch (Exception $e) {
            $this->logger->debug("Error Getting Current Quantity By Sku $sku: " . $e->getMessage());
        }
        return $response;
    }
}
