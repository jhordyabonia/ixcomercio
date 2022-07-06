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
use Magento\Framework\Controller\ResultFactory;

class StockAll extends UnsubscribeController
{
    /**
     * @var \Bss\ProductStockAlert\Model\Stock
     */
    protected $modelStock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $store;

    /**
     * StockAll constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Bss\ProductStockAlert\Model\Stock $modelStock
     * @param \Magento\Store\Model\StoreManagerInterface $store
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Bss\ProductStockAlert\Model\Stock $modelStock,
        \Magento\Store\Model\StoreManagerInterface $store
    ) {
        $this->modelStock = $modelStock;
        $this->store = $store;
        parent::__construct($context, $customerSession);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->modelStock
                ->deleteCustomer(
                    $this->customerSession->getCustomerId(),
                    $this->store
                        ->getStore()
                        ->getWebsiteId()
                );
            $this->messageManager->addSuccessMessage(__('You will no longer receive stock alerts.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to update the alert subscription.'));
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_url->getUrl("productstockalert"));
    }
}
