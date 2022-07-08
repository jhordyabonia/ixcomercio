<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Controller\Unsubscribe;

use Bss\ProductStockAlert\Controller\Unsubscribe as UnsubscribeController;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Stock extends UnsubscribeController
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Bss\ProductStockAlert\Model\Stock
     */
    protected $modelStock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $store;

    /**
     * Stock constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param \Bss\ProductStockAlert\Model\Stock $modelStock
     * @param \Magento\Store\Model\StoreManagerInterface $store
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        \Bss\ProductStockAlert\Model\Stock $modelStock,
        \Magento\Store\Model\StoreManagerInterface $store,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
        $this->modelStock = $modelStock;
        $this->store = $store;
        parent::__construct($context, $customerSession);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $productId = (int)$this->getRequest()->getParam('product_id');
        $parentId = (int)$this->getRequest()->getParam('parent_id');
        $backurl = (int)$this->getRequest()->getParam('backurl');
        $backProductUrl = $backurl;

        if (!$productId) {
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        try {
            $product = $this->productRepository->getById($productId);
            $backProductUrl = $product->getUrlInStore();
            if ($parentId && $productId != $parentId) {
                $parent = $this->productRepository->getById($parentId);
                $backProductUrl = $parent->getUrlInStore();
            }

            if (!$product->isVisibleInCatalog()) {
                throw new NoSuchEntityException(__('The product is not visible now.'));
            }

            $model = $this->doLoadModel($product->getId());
            if ($model->getAlertStockId()) {
                $model->delete();
            }
            $this->messageManager->addSuccessMessage(__('You will no longer receive stock alerts for this product.'));
        } catch (\LogicException $exception) {
            $this->messageManager->addErrorMessage(__('The product was not found.'));
            $resultRedirect->setPath('customer/account/');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the alert subscription right now.'));
        }
        if ($backurl) {
            $resultRedirect->setUrl($this->_url->getUrl("productstockalert"));
            return $resultRedirect;
        }
        $resultRedirect->setUrl($backProductUrl);
        return $resultRedirect;
    }

    /**
     * @param $productId
     * @return \Bss\ProductStockAlert\Model\Stock
     */
    private function doLoadModel($productId)
    {
        if ($this->customerSession->getCustomerId()) {
            return $this->loadModel(
                $this->customerSession->getCustomerId(),
                $productId
            );
        }
        return $this->loadGuestModel(
            $productId
        );
    }

    /**
     * @param int $customerId
     * @param int $productId
     * @return \Bss\ProductStockAlert\Model\Stock
     * @throws NoSuchEntityException
     */
    private function loadModel($customerId, $productId)
    {
        $model = $this->modelStock
            ->setCustomerId($customerId)
            ->setProductId($productId)
            ->setWebsiteId(
                $this->store
                    ->getStore()
                    ->getWebsiteId()
            )->setStoreId(
                $this->store
                    ->getStore()
                    ->getId()
            )
            ->loadByParam();
        return $model;
    }

    /**
     * @param int $productId
     * @return \Bss\ProductStockAlert\Model\Stock
     * @throws NoSuchEntityException
     */
    private function loadGuestModel($productId)
    {
        $notify = $this->customerSession->getNotifySubscription();
        $email = $notify[$productId]['email'];
        $model = $this->modelStock
            ->setCustomerEmail($email)
            ->setProductId($productId)
            ->setWebsiteId(
                $this->store
                    ->getStore()
                    ->getWebsiteId()
            )->setStoreId(
                $this->store
                    ->getStore()
                    ->getId()
            )
            ->loadByParamGuest();
        unset($notify[$productId]);
        $this->customerSession->setNotifySubscription($notify);
        return $model;
    }
}
