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
        if ($this->crocsHelper->isEnabled($product->getStoreId())) {
//            $this->logger->debug($this->registry->registry('flag'));

            if (!$this->registry->registry($product->getSku())) {
                if ($product->getData('mpn')) {
                    $this->_setSku($product);
                    $this->logger->debug($product->getSku() . ': ' . $this->registry->registry($product->getSku()));
//                $this->registry->unregister($product->getSku());
//                $this->registry->register('flag', false);

                    $this->registry->register($product->getSku(), true);
                    $this->logger->debug($product->getSku() . ': ' . $this->registry->registry($product->getSku()));
                    $sku = $this->configurableProduct->getConfigurableSku($product->getData('mpn'), $product->getStoreId());
                    $this->logger->debug($sku);
                    if ($sku) {
                        $this->configurableProduct->createConfigurableProduct($sku, $product);
                        //                $this->logger->debug($product->getStoreId());
                        $isMultiSize = $this->configurableProduct->getIfItIsMultiSize($product->getData('mpn'), $product->getStoreId());
                        $this->logger->debug(json_encode($isMultiSize));
                    }
                } else {
                    $this->logger->debug($product->getSku() . ' Producto sin Mpn');
                }
            } else {
                $this->logger->debug('false: ' . $this->registry->registry('flag'));
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
