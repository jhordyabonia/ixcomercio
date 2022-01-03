<?php namespace Extroniks\CatalogThemeInheritFix\Plugin;

class ProductViewPlugin extends AbstractPlugin
{

    public function beforeExecute(\Magento\Catalog\Controller\Product\View $subject)
    {
        $productId = (int) $subject->getRequest()->getParam('id');
        $categoryId = (int) $subject->getRequest()->getParam('category', false);

        $product = $this->initProduct($productId, $categoryId);
        if ($product) {
            try {
                $settings = $this->catalogDesign->getDesignSettings($product);
                if (!$settings->getCustomDesign()) {
                    $categoryIds = $product->getData('category_ids');
                    if ($categoryIds && is_array($categoryIds) && count($categoryIds)) {
                        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection */
                        $collection = $this->categoryCollectionFactory->create();
                        $collection->addAttributeToSelect('*')
                            ->addAttributeToFilter('entity_id', ['in' => $categoryIds])
                            ->load();

                        foreach ($collection->getItems() as $category) {
                            /** @var \Magento\Catalog\Model\Category $category */
                            try {
                                if ($category->getData('custom_apply_to_products')) {
                                    $settings = $this->catalogDesign->getDesignSettings($category);
                                    if($settings->getCustomDesign()) {
                                        break;
                                    }
                                }
                            } catch (\Exception $e) {
                                // Do nothing
                            }
                        }
                    }
                }
                if ($settings->getCustomDesign()) {
                    $normalizedThemeCode = $this->getNormalizedThemeCode($settings->getCustomDesign());
                    $this->addCustomHandles(['catalog_product_view_' . $normalizedThemeCode]);
                    $this->catalogDesign->applyCustomDesign($settings->getCustomDesign());
                }
            } catch (\Exception $e) {
                // Do nothing
            }
        }

        return [];
    }

    /**
     * Get the product with the given ID, and optionally set the category, if given
     * @param $productId
     * @param $categoryId
     * @return \Magento\Catalog\Model\Product|ProductInterface|bool
     */
    protected function initProduct($productId, $categoryId)
    {
        try {
            $product = $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }

        try {
            $category = $this->categoryRepository->get($categoryId);
            $product->setCategory($category);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Do nothing
        }

        return $product;
    }
}