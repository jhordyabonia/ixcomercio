<?php
declare(strict_types=1);

namespace Intcomex\Crocs\Observer;

use Intcomex\Crocs\Helper\Data;
use Intcomex\Crocs\Model\ConfigurableProduct;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zend\Log\Logger;

class BeforeSaveProduct implements ObserverInterface
{
    /**
     * @var ConfigurableProduct
     */
    protected $configurableProduct;

    /**
     * @var Data
     */
    private $crocsHelper;

    public function __construct(
        ConfigurableProduct $configurableProduct,
        Data $crocsHelper
    ) {
        $this->configurableProduct = $configurableProduct;
        $this->crocsHelper = $crocsHelper;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/crocs.log');
        $this->logger = new Logger();
        $this->logger->addWriter($writer);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $registry = $objectManager->create(\Magento\Framework\Registry::class);
        $this->registry = $registry;
    }

    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getData('product');
        $storeId = $product->getStoreId();

        $this->logger->debug('Sku: ' . $product->getSku() . ' - StoreId: ' . $product->getStoreId());
//        return;
        if ($this->crocsHelper->isEnabled($storeId)) {
//            $this->logger->debug($this->registry->registry('flag'));
            $this->_setSku($product);
            $sku = $product->getSku();
            $mpn = $product->getData('mpn');
            $this->logger->debug('NewSku: ' . $product->getSku() . ' - Mpn: ' . $mpn);

            if (!$this->registry->registry($sku)) {
                if ($mpn) {
                    $this->logger->debug($sku . ' Updated: ' . $this->registry->registry($sku));
                    $this->registry->register($sku, true);

                    $configurableSku = $this->configurableProduct->getConfigurableSku($mpn, $storeId);
                    if ($configurableSku) {
                        $this->configurableProduct->createConfigurableProduct($configurableSku, $product);
                        //                $this->logger->debug($storeId);
                        $color = $this->configurableProduct->getColor($mpn, $storeId);
                        $this->logger->debug($color);

                        $sizes = $this->configurableProduct->getSizes($mpn, $storeId);
                        $this->logger->debug(json_encode($sizes));

                        // Set data to First product
                        $this->configurableProduct->setDataToFirstProduct($product, $sizes[0], $color);

                        // If it is multi size
                        if (count($sizes) > 1) {
                            // Set data to Woman product
//                            $this->configurableProduct->createSecondProduct($product, $sizes[1], $color);
                        }
                    }
                } else {
                    $this->logger->debug($sku . ' Producto sin Mpn');
                }
            } else {
                $this->logger->debug($sku . ' Updated: ' . $this->registry->registry($sku));
            }
        }
    }

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
