<?php

namespace Intcomex\CompareProducts\Plugin\Controller\Product\Compare;

use Magento\Catalog\Controller\Product\Compare\Add as MagentoAdd;
use Magento\Catalog\Model\Product;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Add extends MagentoAdd
{
    /**
     * @param MagentoAdd $subject
     * @param $result
     * @return false|ResultInterface
     * @throws NoSuchEntityException
     */
    public function afterExecute(MagentoAdd $subject, $result)
    {
        // Get current category & current compare data
        $currentCategoryId = (int)$this->_catalogSession->getData('last_viewed_category_id') ?? $this->_catalogSession->getData('last_visited_category_id');
        $currentCompareCategoryId = (int)$this->_catalogSession->getCurrentCompareCategoryId();
        $compareItemsCount = (int)$this->_catalogSession->getData('catalog_compare_items_count');

        // Array response
        $response = [
            'success' => true,
            'popup'   => ''
        ];
        $popup = $this->_view->getLayout()
            ->createBlock('Magento\Catalog\Block\Product\Compare\ListCompare')
            ->setTemplate('Intcomex_CompareProducts::product/mini-compare/list.phtml');

        // If request is ajax
        if ($subject->getRequest()->isAjax()) {
            $this->_view->loadLayout();
            $productId = (int)$this->getRequest()->getParam('product');
            $storeId = $this->_storeManager->getStore()->getId();

            try {
                /** @var Product $product */
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }

            // Validates if already have the maximum number of products
            if ($compareItemsCount === 4) {
                $response['popup'] = $popup->setData('isLimitReached', true)->toHtml();
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($response);
                return $resultJson;
            }

            // Validates if current category is current compare category
            if ($currentCategoryId !== $currentCompareCategoryId && $compareItemsCount !== 0) {
                $response['popup'] = $popup->setData('isNotSameCategory', true)->toHtml();
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($response);
                return $resultJson;
            }

            if ($product) {
                // Add product to compare list
                $this->_catalogProductCompareList->addProduct($product);
                $this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);
                $this->_objectManager->get(\Magento\Catalog\Helper\Product\Compare::class)->calculate();
                $response['popup'] = $popup->setData('product', $product)->toHtml();

                // Set current compare category id
                $this->_catalogSession->setCurrentCompareCategoryId($currentCategoryId);

                // Set response
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($response);
                return $resultJson;
            }

            return false;
        }

        return $result;
    }
}
