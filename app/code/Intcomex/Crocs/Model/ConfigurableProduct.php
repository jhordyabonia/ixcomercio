<?php
declare(strict_types=1);

namespace Intcomex\Crocs\Model;

use Intcomex\Crocs\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
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

    /**
     * @var string[]
     */
    private $configurableAttributes = ['crocs_color', 'crocs_gender', 'crocs_size'];

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

    public function getIsModuleEnabled($storeId)
    {
        if ($this->crocsHelper->isEnabled($storeId)) {
            return true;
        }
        return false;
    }

    public function getSkuWithPrefixIfNeeded($sku, $storeId): string
    {
        $isEnabled = $this->crocsHelper->isEnabled($storeId);
        $prefix = $this->crocsHelper->getPrefix($storeId);
        if ($isEnabled && strpos($sku, $prefix) === false) {
            return $prefix . $sku;
        }
        return $sku;
    }

    public function getConfigurableSku($mpn, $storeId)
    {
        $separator = $this->crocsHelper->getSeparator($storeId);
        if (strpos($mpn, $separator) !== false) {
            $mpnExploded = explode($separator, $mpn);
            return $this->crocsHelper->getPrefix($storeId) . $mpnExploded[0];
        }
        return false;
    }

    public function getSizes($mpn, $storeId): array
    {
        $this->logger->info('MPN:: ' . $mpn . " StoreId: $storeId");
        $separator = $this->crocsHelper->getSeparator($storeId);
        $defaultStoreView = null;
        $mpnExploded = explode($separator, $mpn);
        $lastPart = $mpnExploded[count($mpnExploded)-1];
        $this->logger->info($lastPart);

        $attribute = $this->eavConfig->getAttribute('catalog_product', 'crocs_sizes_match');
        $options = $attribute->setStoreId(0)->getSource()->getAllOptions();
        $this->logger->info(json_encode($options));
        foreach ($options as $option) {
            //$this->logger->info("0::: " . $attribute->setStoreId(0)->getSource()->getOptionText($option['value']));
            //$this->logger->info("1::: " . $attribute->setStoreId(1)->getSource()->getOptionText($option['value']));
            if ($option['label'] === $lastPart) {
                $defaultStoreView = $attribute->setStoreId(1)->getSource()->getOptionText($option['value']);
            }
        }
        $this->logger->info($defaultStoreView);
        // If it is multi size
        if ($defaultStoreView) {
            $defaultStoreViewExploded = explode($separator, $defaultStoreView);
            // Position 0 is Man and 1 is Woman
            return [$defaultStoreViewExploded[0], $defaultStoreViewExploded[1]];
        } else {
            return [$lastPart];
        }
    }

    public function getColor($mpn, $storeId)
    {
        $separator = $this->crocsHelper->getSeparator($storeId);
        $mpnExploded = explode($separator, $mpn);
        return $mpnExploded[1];
    }

    public function createOrUpdateConfigurableProduct($sku, Product $product)
    {
        $configurableProductId = null;
        try {
            $configurableProduct = $this->productRepository->get($sku, true);
            $configurableProductId = $configurableProduct->getId();
            $isNewConfigurableProduct = false;
            $this->logger->debug("Ya existe el producto configurable con Sku: $sku");
        } catch (NoSuchEntityException $e) {
            $isNewConfigurableProduct = true;

            /** @var Product $configurableProduct */
            $configurableProduct = $this->productFactory->create();
            $configurableProduct->setSku($sku);
            // $configurableProduct->setName($product->getName()); @todo
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

            try {
                $configurableProduct->save();
                $configurableProductId = $configurableProduct->getId();
            } catch (\Exception $e) {
                $this->logger->info('Error creating configurable product:: ' . $e->getMessage());
            }
        }

        if ($configurableProductId) {
            try {
                // Attributes to variations
                $color_attr_id = $configurableProduct->getResource()->getAttribute('crocs_color')->getId();
                $size_attr_id = $configurableProduct->getResource()->getAttribute('crocs_size')->getId();
                $gender_attr_id = $configurableProduct->getResource()->getAttribute('crocs_gender')->getId();
                $configurableProduct->getTypeInstance()->setUsedProductAttributeIds([$color_attr_id, $size_attr_id, $gender_attr_id], $configurableProduct);

                $configurableAttributesData = $configurableProduct->getTypeInstance()->getConfigurableAttributesAsArray($configurableProduct);
                $configurableProduct->setCanSaveConfigurableAttributes(true);
                $configurableProduct->setConfigurableAttributesData($configurableAttributesData);

                // Set Child Products
                $children = [];
                foreach ($configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct) as $child) {
                    $children[] = $child->getId();
                }
                $configurableProduct->setAssociatedProductIds(array_merge($children, [$product->getId()]));
                $configurableProduct->setCanSaveConfigurableAttributes(true);
                $configurableProduct->save();

                if ($isNewConfigurableProduct) {
                    $this->logger->debug("Created ConfigurableProductId: $configurableProductId AssociatedProductId: " . $product->getId());
                } else {
                    $this->logger->debug("Updated ConfigurableProductId: $configurableProductId AssociatedProductId: " . $product->getId());
                }
            } catch (\Exception $e) {
                $this->logger->info('Error assign child to configurable product:: ' . $e->getMessage());
            }
        }
    }

    public function setDataToManProduct(Product $product, $size, $color, $isMultiSize)
    {
        $separator = $this->crocsHelper->getSeparator($product->getStoreId());
        $options = $this->_getAllAttributeOptions();
        $skuExploded = explode($separator, $product->getSku());
        $skuLastPart = $skuExploded[count($skuExploded)-1];
        $skuLastPartToPlus = ($isMultiSize) ? $separator . $size : '';

        if ($skuLastPart !== $size) $product->setSku($product->getSku() . $skuLastPartToPlus);
        $product->setVisibility(1);
        $product->setCrocsColor($options[$this->configurableAttributes[0]][$color]);
        $product->setCrocsGender($options[$this->configurableAttributes[1]][$this->_getGenderBySize($size)]);
        $product->setCrocsSize($options[$this->configurableAttributes[2]][$size]);
        $product->save();
    }

    public function setDataToWomanProduct(Product $product, $size, $color)
    {
        $this->logger->debug("Sku: " . $product->getSku() . " Size: $size" . " Color: $color");
        $separator = $this->crocsHelper->getSeparator($product->getStoreId());
        $skuExploded = explode($separator, $product->getSku());
        $sku = $skuExploded[0] . $separator . $skuExploded[1] . $separator . $size;
        $options = $this->_getAllAttributeOptions();
        $this->logger->debug("NewSku: " . $sku);
        try {
            $secondProduct = $this->productRepository->get($sku, false);
            $isNewProduct = false;
        } catch (NoSuchEntityException $e) {
            $secondProduct = $this->productFactory->create();
            $secondProduct->setUrlKey(html_entity_decode(strip_tags(strtolower(rand(0, 1000) . '-' . $product->getName() . '-' . $product->getSku() . '-' . $product->getStoreId()))));
            $isNewProduct = true;
        }
        $this->logger->debug("IsNewSku?: " . $isNewProduct);
        $secondProduct->setSku($sku);
        $secondProduct->setData('mpn', $product->getData('mpn'));
        $secondProduct->setAttributeSetId($product->getAttributeSetId());
        $secondProduct->setTypeId('simple');
        $secondProduct->setVisibility(1);
        $secondProduct->setWebsiteIds($product->getWebsiteIds());
        $secondProduct->setCategoryIds($product->getCategoryIds());
        $secondProduct->setStockData([
            'use_config_manage_stock' => 0,
            'manage_stock' => 1,
            'is_in_stock' => 1,
        ]);
        $secondProduct->setCrocsColor($options[$this->configurableAttributes[0]][$color]);
        $secondProduct->setCrocsGender($options[$this->configurableAttributes[1]][$this->_getGenderBySize($size)]);
        $secondProduct->setCrocsSize($options[$this->configurableAttributes[2]][$size]);
        $this->logger->debug(json_encode($secondProduct->getData()));

        try {
            $secondProduct->save();
            if ($isNewProduct) {
                $this->logger->debug('Second Product Created: ' . $secondProduct->getSku());
            } else {
                $this->logger->debug('Second Product Updated: ' . $secondProduct->getSku());
            }
        } catch (\Exception $e) {
            $this->logger->info('Error Creating Woman Product: ' . $e->getMessage());
        }
    }

    private function _getGenderBySize($size)
    {
        if (str_contains($size, 'M')) return 'Man';
        if (str_contains($size, 'W')) return 'Woman';
        if (str_contains($size, 'C') || str_contains($size, 'J')) return 'Kid';
    }

    private function _getAllAttributeOptions(): array
    {
        $options = [];
        try {
            foreach ($this->configurableAttributes as $attribute) {
                $eavAttribute = $this->eavConfig->getAttribute('catalog_product', $attribute);
                $allOptions = $eavAttribute->getSource()->getAllOptions();
                foreach ($allOptions as $option) {
                    $options[$attribute][$eavAttribute->setStoreId(0)->getSource()->getOptionText($option['value'])] = $option['value'];
                }
            }
        } catch (\Exception $e) {
            $options = [];
            $this->logger->debug('Error obteniendo las opciones de atributos: ' . $e->getMessage());
        }
        return $options;
    }
}
