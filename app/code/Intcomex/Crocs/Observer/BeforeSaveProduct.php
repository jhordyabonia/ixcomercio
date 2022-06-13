<?php
declare(strict_types=1);

namespace Intcomex\Crocs\Observer;

use Exception;
use Intcomex\Crocs\Helper\Data;
use Intcomex\Crocs\Model\ConfigurableProduct;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Zend\Log\Logger;

class BeforeSaveProduct implements ObserverInterface
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
     * @param ConfigurableProduct $configurableProduct
     * @param Data $crocsHelper
     */
    public function __construct(
        ConfigurableProduct $configurableProduct,
        Data $crocsHelper
    ) {
        $this->configurableProduct = $configurableProduct;
        $this->crocsHelper = $crocsHelper;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/crocs.log');
        $this->logger = new Logger();
        $this->logger->addWriter($writer);
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getData('product');
        $genericName = $observer->getData('generic_name');
        $storeId = $product->getStoreId();
        $separator = $this->crocsHelper->getSeparator($product->getStoreId());
        $this->logger->debug('Sku: ' . $product->getSku() . ' - StoreId: ' . $product->getStoreId());

        if ($this->crocsHelper->isEnabled($storeId)) {
            try {
                $mpn = $product->getData('mpn');

                if ($mpn) {
                    $configurableSku = $this->configurableProduct->getConfigurableSku($mpn, $storeId);
                    if ($configurableSku) {
                        $this->_setSku($product, false);
                        $this->logger->debug('NewSku: ' . $product->getSku() . ' - Mpn: ' . $mpn);
                        $color = $this->configurableProduct->getColor($mpn, $storeId);
                        $sizes = $this->configurableProduct->getSizes($mpn, $storeId);
                        $this->logger->debug('ConfigurableSku: ' . $configurableSku . ' Color: ' . $color . ' Sizes: ' . json_encode($sizes));

                        // Set data to First product / If editing product is Woman set in the first position
                        $skuExploded = explode($separator, $product->getSku());
                        if (isset($skuExploded[2]) && str_contains($skuExploded[2], 'W')) {
                            $sizes[0] = $skuExploded[2];
                        }
                        $this->configurableProduct->setDataToFirstProduct($product, $sizes[0], $color, count($sizes) > 1);
                        $skuExploded = explode($separator, $product->getSku());
                        $womanProductId = null;
                        // If it is multi size Man or Kid
                        if (count($sizes) > 1 && isset($skuExploded[2]) && (str_contains($skuExploded[2], 'M') || str_contains($skuExploded[2], 'C'))) {
                            // Set data to Woman product
                            $womanProductId = $this->configurableProduct->setDataToWomanProduct($product, $sizes[1], $color);
                        }
                        // Create Configurable Product
                        $this->configurableProduct->createOrUpdateConfigurableProduct($configurableSku, $product, $womanProductId, $genericName);
                    } else {
                        $this->_setSku($product, true);
                        $this->logger->debug($product->getSku() . ' Producto No Configurable');
                    }
                } else {
                    $this->logger->debug($product->getSku() . ' Producto Sin Mpn');
                }
            } catch (Exception $e) {
                $this->logger->debug('Error Crocs BeforeSaveProduct Observer: ' . $e->getMessage());
            }
        }
    }

    /**
     * @param Product $product
     * @param bool $save
     * @throws Exception
     */
    private function _setSku(Product $product, bool $save)
    {
        $prefix = $this->crocsHelper->getPrefix($product->getStoreId());
        if (strpos($product->getSku(), $prefix) !== false) {
            $product->setSku($product->getSku());
        } else {
            $product->setSku($prefix . $product->getSku());
        }
        if ($save) {
            $product->save(); // Save Sku
        }
    }
}
