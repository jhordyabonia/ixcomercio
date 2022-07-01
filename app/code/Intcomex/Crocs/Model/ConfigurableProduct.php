<?php
declare(strict_types=1);

namespace Intcomex\Crocs\Model;

use Exception;
use Intcomex\Crocs\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
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

    /**
     * @param Data $crocsHelper
     * @param Config $eavConfig
     * @param ProductRepositoryInterface $productRepository
     * @param ProductFactory $productFactory
     */
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

    /**
     * @param $storeId
     * @return bool
     */
    public function getIsModuleEnabled($storeId)
    {
        if ($this->crocsHelper->isEnabled($storeId)) {
            return true;
        }
        return false;
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getSeparator($storeId)
    {
        return $this->crocsHelper->getSeparator($storeId);
    }

    /**
     * @param $sku
     * @param $storeId
     * @return string
     */
    public function getSkuWithPrefixIfNeeded($sku, $storeId): string
    {
        $isEnabled = $this->crocsHelper->isEnabled($storeId);
        $prefix = $this->crocsHelper->getPrefix($storeId);
        if ($isEnabled && strpos($sku, $prefix) === false) {
            return $prefix . $sku;
        }
        return $sku;
    }

    /**
     * @param $mpn
     * @param $storeId
     * @return false|string
     */
    public function getConfigurableSku($mpn, $storeId)
    {
        $separator = $this->crocsHelper->getSeparator($storeId);
        if (strpos($mpn, $separator) !== false) {
            $mpnExploded = explode($separator, $mpn);
            return $this->crocsHelper->getPrefix($storeId) . $mpnExploded[0];
        }
        return false;
    }

    /**
     * @param $mpn
     * @param $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getSizes($mpn, $storeId): array
    {
        $separator = $this->crocsHelper->getSeparator($storeId);
        $defaultStoreView = null;
        $mpnExploded = explode($separator, $mpn);
        $lastPart = $mpnExploded[count($mpnExploded)-1];

        $attribute = $this->eavConfig->getAttribute('catalog_product', 'crocs_sizes_match');
        $options = $attribute->setStoreId(0)->getSource()->getAllOptions();

        foreach ($options as $option) {
            if ($option['label'] === $lastPart) {
                $defaultStoreView = $attribute->setStoreId(1)->getSource()->getOptionText($option['value']);
            }
        }

        // If it is multi size
        if ($defaultStoreView) {
            $defaultStoreViewExploded = explode($separator, $defaultStoreView);
            // Position 0 is Man and 1 is Woman
            return [$defaultStoreViewExploded[0], $defaultStoreViewExploded[1]];
        } else {
            return [$lastPart];
        }
    }

    /**
     * @param $mpn
     * @param $storeId
     * @return mixed|string
     */
    public function getColor($mpn, $storeId)
    {
        $separator = $this->crocsHelper->getSeparator($storeId);
        $mpnExploded = explode($separator, $mpn);
        return $mpnExploded[1];
    }

    /**
     * @param $sku
     * @param Product $product
     * @param $womanProductId
     * @param $genericName
     */
    public function createOrUpdateConfigurableProduct($sku, Product $product, $womanProductId, $genericName)
    {
        $configurableProductId = null;
        try {
            $configurableProduct = $this->productRepository->get($sku, true, $product->getStoreId(), true);
            $configurableProductId = $configurableProduct->getId();
            $configurableProduct->setName($genericName);
            $configurableProduct->save();
            $isNewConfigurableProduct = false;
        } catch (NoSuchEntityException $e) {
            $isNewConfigurableProduct = true;

            /** @var Product $configurableProduct */
            $configurableProduct = $this->productFactory->create();
            $configurableProduct->setSku($sku);
            $configurableProduct->setName($genericName);
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
            } catch (Exception $e) {
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

                // Adds $children (already has) $product (Man Product) & $womanProductId (Woman Product if is not null)
                $productsToAdd = [];
                $productsToAdd[] = $product->getId();
                if ($womanProductId) {
                    $productsToAdd[] = $womanProductId;
                }
                $associatedProductIds = array_merge($children, $productsToAdd);
                $configurableProduct->setAssociatedProductIds($associatedProductIds);
                $configurableProduct->setCanSaveConfigurableAttributes(true);
                $configurableProduct->save();

                if ($isNewConfigurableProduct) {
                    $this->logger->debug("Created ConfigurableProductId: $configurableProductId AssociatedProductIds: " . json_encode($associatedProductIds));
                } else {
                    $this->logger->debug("Updated ConfigurableProductId: $configurableProductId AssociatedProductIds: " . json_encode($associatedProductIds));
                }
            } catch (Exception $e) {
                $this->logger->info('Error assign child to configurable product:: ' . $e->getMessage());
            }
        }
    }

    /**
     * @param Product $product
     * @param $size
     * @param $color
     * @param $isMultiSize
     * @throws Exception
     */
    public function setDataToFirstProduct(Product $product, $size, $color, $isMultiSize)
    {
        $separator = $this->crocsHelper->getSeparator($product->getStoreId());
        $options = $this->_getAllAttributeOptions();
        $skuExploded = explode($separator, $product->getSku());
        $skuLastPart = $skuExploded[count($skuExploded)-1];
        $skuLastPartToPlus = ($isMultiSize) ? $separator . $size : '';

        if ((count($skuExploded) === 2 && $skuLastPart !== $size) || (isset($skuExploded[2]) && (str_contains($skuExploded[2], 'M') || str_contains($skuExploded[2], 'C')) && $skuLastPart !== $size)) {
            $product->setSku($product->getSku() . $skuLastPartToPlus);
        }
        $product->setVisibility(1);
        $product->setCrocsColor($options[$this->configurableAttributes[0]][$color]);
        $product->setCrocsGender($options[$this->configurableAttributes[1]][$this->_getGenderBySize($size)]);
        $product->setCrocsSize($options[$this->configurableAttributes[2]][$size]);
        $product->save();
    }

    /**
     * @param Product $product
     * @param $size
     * @param $color
     * @return int|void|null
     */
    public function setDataToWomanProduct(Product $product, $size, $color)
    {
        $separator = $this->crocsHelper->getSeparator($product->getStoreId());
        $skuExploded = explode($separator, $product->getSku());
        $sku = $skuExploded[0] . $separator . $skuExploded[1] . $separator . $size;
        $options = $this->_getAllAttributeOptions();

        try {
            $secondProduct = $this->productRepository->get($sku, true, $product->getStoreId());
            $isNewProduct = false;
        } catch (NoSuchEntityException $e) {
            $secondProduct = $this->productFactory->create();
            $secondProduct->setUrlKey(html_entity_decode(strip_tags(strtolower(rand(0, 1000) . '-' . $product->getName() . '-' . $product->getSku() . '-' . $product->getStoreId()))));
            $isNewProduct = true;
        }

        $secondProduct->setSku($sku);
        $secondProduct->setData('mpn', $product->getData('mpn'));
        $secondProduct->setName($product->getName());
        $secondProduct->setPrice($product->getPrice());
        $secondProduct->setSpecialPrice($product->getSpecialPrice());
        $secondProduct->setSpecialFromDate($product->getSpecialFromDate());
        $secondProduct->setSpecialToDate($product->getSpecialToDate());
        $secondProduct->setAttributeSetId($product->getAttributeSetId());
        $secondProduct->setTypeId('simple');
        $secondProduct->setVisibility(1);
        $secondProduct->setWebsiteIds($product->getWebsiteIds());
        $secondProduct->setCategoryIds($product->getCategoryIds());
        $secondProduct->setWeight($product->getWeight());
        $secondProduct->setTsDimensionsHeight($product->getTsDimensionsHeight());
        $secondProduct->setTsDimensionsLength($product->getTsDimensionsLength());
        $secondProduct->setTsDimensionsWidth($product->getTsDimensionsWidth());
        $secondProduct->setStockData([
            'use_config_manage_stock' => 0,
            'manage_stock' => 1,
            'is_in_stock' => 1,
        ]);
        $secondProduct->setCrocsColor($options[$this->configurableAttributes[0]][$color]);
        $secondProduct->setCrocsGender($options[$this->configurableAttributes[1]][$this->_getGenderBySize($size)]);
        $secondProduct->setCrocsSize($options[$this->configurableAttributes[2]][$size]);
        $secondProduct->setCrocsFit($product->getCrocsFit());
        $secondProduct->setCrocsStyle($product->getCrocsStyle());
        $secondProduct->setPaisDeOrigen($product->getPaisDeOrigen());
        $secondProduct->setMaterialCrocs($product->getMaterialCrocs());
        $secondProduct->setGarantia($product->getGarantia());
        $secondProduct->setTipoDeProductoCrocs($product->getTipoDeProductoCrocs());

        try {
            $secondProduct->save();
            if ($isNewProduct) {
                $this->logger->debug('Woman Product Created: ' . $secondProduct->getSku());
            } else {
                $this->logger->debug('Woman Product Updated: ' . $secondProduct->getSku());
            }
            return $secondProduct->getId();
        } catch (Exception $e) {
            $this->logger->info('Error Creating Woman Product: ' . $e->getMessage());
        }
    }

    /**
     * @param $size
     * @return string|void
     */
    private function _getGenderBySize($size)
    {
        if (str_contains($size, 'M')) return 'Man';
        if (str_contains($size, 'W')) return 'Woman';
        if (str_contains($size, 'C') || str_contains($size, 'J')) return 'Kid';
    }

    /**
     * @return array
     */
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
        } catch (Exception $e) {
            $options = [];
            $this->logger->debug('Error obteniendo las opciones de atributos: ' . $e->getMessage());
        }
        return $options;
    }
}
