<?php
declare(strict_types=1);

namespace Intcomex\CompareProducts\Controller\Product\Compare;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class RemoveAll extends \Magento\Catalog\Controller\Product\Compare implements HttpPostActionInterface
{
    /**
     * Remove all items from compare list.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setRefererUrl();
        }

        $compareHelper = $this->_objectManager->get(\Magento\Catalog\Helper\Product\Compare::class);
        $items = $compareHelper->getItemCollection()->getItems();
        foreach ($items as $item) {
            $productId = $item->getData('product_id');
            $storeId = $this->_storeManager->getStore()->getId();

            try {
                /** @var Product $product */
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }

            if ($product) {
                $this->_catalogProductCompareList->removeProduct($product);
                $this->_eventManager->dispatch('catalog_product_compare_remove_product', ['product' => $product]);
            }

        }

        return $resultRedirect->setRefererOrBaseUrl();
    }
}
