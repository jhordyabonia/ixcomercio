<?php

namespace Intcomex\CredomaticMSI\Helper;

use Magento\Catalog\Model\Product;
use Intcomex\CredomaticMSI\Model\ResourceModel\CampaignDetail\CollectionFactory;
class UpdateFeeAttributeHelper
{
    /**
     * @var Product 
     */
    private $product;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productLoader;

    /**
     * @param Product $product
     */
    public function __construct(
        Product $product,
        CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productLoader
    ){
        $this->product = $product;
        $this->_collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->productLoader = $productLoader;
    }

    /**
     * @param $data
     * @return void
     * @throws \Exception
     */
    public function update($data, $edit)
    {
        $productFees = [];
        $productResource = $this->product->getResource();
        $productId = $this->product->getIdBySku($data['sku']);
        if ($productId) {
            $this->product->load($productId);
            $attributeCode = 'cuotas';
            $attribute = $productResource->getAttribute($attributeCode);
            $productFees = $this->product->getCuotas();
            $productFees = array_filter(explode(',', $productFees));
            $fee = $attribute->getSource()->getOptionId($data['fee']);
              
            if ($edit && !in_array($fee, $productFees)) {
                $getDetailsSku = $this->_collectionFactory->create()->getDetailsSku($data['sku'],$data['campaign_id'])->getData();
                if (count($getDetailsSku) < 2) {
                    $productFees = [];
                }
            }
                    
            if ($attribute->usesSource()) {
                $productFees[] = $fee;
                $this->product->setData($attributeCode, $productFees);
            }
        }
        $this->product->save();
    }

    public function delete($data)
    {
        //$storeId = $this->storeManager->getStore()->getId();
        $storeId = $this->product->getStoreId();
        $productResource = $this->product->getResource();
        $productId = $this->product->getIdBySku($data['sku']);
        if ($productId) {
            $productObject = $this->productLoader->create()->setStoreId($storeId)->load($productId);
            $attributeCode = 'cuotas';
            $attribute = $productResource->getAttribute($attributeCode);
            $productFees = $productObject->getCuotas();
            $productFees = array_filter(explode(',', $productFees));
            $fee = $attribute->getSource()->getOptionId($data['fee']);
            if ($attribute->usesSource()) {
                if (in_array($fee, $productFees)) {
                    unset($productFees[array_search($fee, $productFees)]);
                    $productObject->setData($attributeCode, $productFees);
                }
            }
            $productObject->save();
        }
    }
}
