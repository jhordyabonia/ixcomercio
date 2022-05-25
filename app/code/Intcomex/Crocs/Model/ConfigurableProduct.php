<?php
declare(strict_types=1);

namespace Intcomex\Crocs\Model;

use Intcomex\Crocs\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validator\Exception;
use Zend\Log\Logger;

class ConfigurableProduct
{
    /**
     * @var Data
     */
    private $crocsHelper;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    public function __construct(
        Data $crocsHelper,
        Config $eavConfig,
        ProductRepositoryInterface $productRepository,
        ProductFactory $productFactory
    ) {
        $this->crocsHelper = $crocsHelper;
        $this->eavConfig = $eavConfig;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/crocs.log');
        $this->logger = new Logger();
        $this->logger->addWriter($writer);
    }

    public function getConfigurableSku($mpn, $storeId)
    {
        $this->logger->debug($mpn);
        $this->logger->debug($storeId);
        $separator = $this->crocsHelper->getSeparator($storeId);
        $this->logger->debug($separator);
        if (strpos($mpn, $separator) !== false) {
            $mpnExploded = explode($separator, $mpn);
            return $this->crocsHelper->getPrefix($storeId) . $mpnExploded[0];
        }
        return false;
    }

    public function getIfItIsMultiSize($mpn, $storeId)
    {
        $separator = $this->crocsHelper->getSeparator($storeId);
        $defaultStoreView = null;
        $mpnExploded = explode($separator, $mpn);
        $lastPart = $mpnExploded[count($mpnExploded)-1];
        $this->logger->debug($lastPart);

        $attribute = $this->eavConfig->getAttribute('catalog_product', 'crocs_sizes_match');
        $options = $attribute->getSource()->getAllOptions();
        $this->logger->debug(json_encode($options));
        foreach ($options as $option) {
            if ($option['label'] === $lastPart) {
                $defaultStoreView = $attribute->setStoreId(1)->getSource()->getOptionText($option['value']);
                $this->logger->debug($defaultStoreView);
            }
        }

        if ($defaultStoreView) {
            $defaultStoreViewExploded = explode($separator, $defaultStoreView);
            return [
                'M' => $defaultStoreViewExploded[0],
                'W' => $defaultStoreViewExploded[1]
            ];
        }

        return false;
    }

    public function createConfigurableProduct($sku, Product $product)
    {
        $configurableProductId = null;
        try {
            $configurableProduct = $this->productRepository->get($sku, false);
            $configurableProductId = $configurableProduct->getId();
            $this->logger->debug("Ya existe el producto configurable con Sku: $sku");
        } catch (NoSuchEntityException $e) {


                /** @var Product $configurableProduct */
                $configurableProduct = $this->productFactory->create();

                $configurableProduct->setSku($sku);
//                $configurableProduct->setUrlKey($this->crocsHelper->getPrefix($product->getStoreId()) . $sku);
//                $configurableProduct->setName($product->getName());
                $configurableProduct->setAttributeSetId($product->getAttributeSetId());
                $configurableProduct->setStatus(1);
                $configurableProduct->setTypeId('configurable');
                $configurableProduct->setVisibility(4);
                $configurableProduct->setWebsiteIds($product->getWebsiteIds());
                $configurableProduct->setCategoryIds($product->getCategoryIds());
                $configurableProduct->setStockData([
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'is_in_stock' => 1,
                ]);

                $configurableProduct->setUrlKey(html_entity_decode(strip_tags(strtolower(rand(0, 1000) . '-' . $product->getName() . '-' . $product->getSku() . '-' . $product->getStoreId()))));

//                $configurableProductsData = array();
//                $configurableProduct->setConfigurableProductsData($configurableProductsData);
                try {
                    $configurableProduct->save();
                    $configurableProductId = $configurableProduct->getId();
                } catch (\Exception $e) {
                    $this->logger->info('Error 1:: ' . $e->getMessage());
                }


        }

        $this->logger->debug("ConfigurableProductId: $configurableProductId");
        if ($configurableProductId) {
            try {
//                $configurableProduct = $this->productFactory->create()->load($configurableProductId);
//                $configurableProduct->setTypeId('configurable');

                // Attributes to variations
                $color_attr_id = $configurableProduct->getResource()->getAttribute('crocs_color')->getId();
                $size_attr_id = $configurableProduct->getResource()->getAttribute('crocs_size')->getId();
                $gender_attr_id = $configurableProduct->getResource()->getAttribute('crocs_gender')->getId();
                $configurableProduct->getTypeInstance()->setUsedProductAttributeIds([$color_attr_id, $size_attr_id, $gender_attr_id], $configurableProduct);

                $configurableAttributesData = $configurableProduct->getTypeInstance()->getConfigurableAttributesAsArray($configurableProduct);
                $configurableProduct->setCanSaveConfigurableAttributes(true);
                $configurableProduct->setConfigurableAttributesData($configurableAttributesData);

                $configurableProduct->setAssociatedProductIds([$product->getId()]);
                $configurableProduct->setCanSaveConfigurableAttributes(true);
                $configurableProduct->save();
                $this->logger->debug('OK!');
            } catch (Exception $e) {
                $this->logger->info('Error 2:: ' . $e->getMessage());
            }
        }

        $this->logger->info('End!');
    }
}
