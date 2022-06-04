<?php
declare(strict_types=1);

namespace Intcomex\Crocs\Observer;

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
        $this->logger->debug('Sku: ' . $product->getSku() . ' - StoreId: ' . $product->getStoreId());

        if ($this->crocsHelper->isEnabled($storeId)) {
            $this->_setSku($product);
            $sku = $product->getSku();
            $mpn = $product->getData('mpn');
            $this->logger->debug('NewSku: ' . $product->getSku() . ' - Mpn: ' . $mpn);

            if ($mpn) {
                $configurableSku = $this->configurableProduct->getConfigurableSku($mpn, $storeId);
                if ($configurableSku) {
                    $color = $this->configurableProduct->getColor($mpn, $storeId);
                    $sizes = $this->configurableProduct->getSizes($mpn, $storeId);
                    $this->logger->debug('ConfigurableSku: ' . $configurableSku . ' Color: ' . $color . ' Sizes: ' . json_encode($sizes));
                    // Set data to Man product
                    $this->configurableProduct->setDataToManProduct($product, $sizes[0], $color, count($sizes) > 1);
                    // If it is multi size
                    $womanProductId = null;
                    if (count($sizes) > 1) {
                        // Set data to Woman product
                        $womanProductId = $this->configurableProduct->setDataToWomanProduct($product, $sizes[1], $color);
                    }
                    // Create Configurable Product
                    $this->configurableProduct->createOrUpdateConfigurableProduct($configurableSku, $product, $womanProductId, $genericName);
                } else {
                    $this->logger->debug($sku . ' Producto No Configurable');
                }
            } else {
                $this->logger->debug($sku . ' Producto Sin Mpn');
            }
        }
    }

    /**
     * @param Product $product
     */
    private function _setSku(Product $product)
    {
        $prefix = $this->crocsHelper->getPrefix($product->getStoreId());
        if (strpos($product->getSku(), $prefix) !== false) {
            $product->setSku($product->getSku());
        } else {
            $product->setSku($prefix . $product->getSku());
        }
    }
}
