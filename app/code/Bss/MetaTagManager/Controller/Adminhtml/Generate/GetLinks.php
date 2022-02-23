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
 * @package    Bss_MetaTagManager
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MetaTagManager\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class GetLinks
 * @package Bss\MetaTagManager\Controller\Adminhtml\Generate
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class GetLinks extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\MetaTagManager\Model\metaTemplateFactory
     */
    private $metaTemplateFactory;

    /**
     * @var \Bss\MetaTagManager\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * GetLinks constructor.
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory
     * @param \Bss\MetaTagManager\Model\RuleFactory $ruleFactory
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory,
        \Bss\MetaTagManager\Model\RuleFactory $ruleFactory,
        Context $context
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->metaTemplateFactory = $metaTemplateFactory;
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $dataReturn = [
            "status" => false,
            "data" => [],
            "error_type" => ""
        ];
        if ($this->getRequest()->isAjax()) {
            $page = $this->getRequest()->getPost('page');
            $metaTemplateId = $this->getRequest()->getPost('meta_template_id');
            $productCollection = $this->getProductCollection($metaTemplateId, $page);
            $linkData = [];
            foreach ($productCollection as $product) {
                $dataToAdd = [
                    'name' => $product->getName(),
                    'id' => $product->getId()
                ];
                if ($product->getData('excluded_meta_template')) {
                    $dataToAdd['is_excluded'] = true;
                } else {
                    $dataToAdd['is_excluded'] = false;
                }
                $linkData[] = $dataToAdd;
            }
            if (!empty($linkData)) {
                $dataReturn['status'] = true;
                $dataReturn['data'] = $linkData;
            } else {
                $dataReturn['error_type'] = 'empty';
            }
        } else {
            $dataReturn['error_type'] = 'bad_request';
        }

        $result->setData($dataReturn);
        return $result;
    }

    /**
     * @param string $id
     * @param int $page
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection($id, $page = 1)
    {
        $model = $this->metaTemplateFactory->create();
        if ($id) {
            $model->load($id);
            $dataReturn = $model->getData();
            $modelRule = $this->ruleFactory->create();
            $modelRule->loadPost($dataReturn);
            $productCollection = $modelRule->getProductCollection($page);
            return $productCollection;
        }
        return [];
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_MetaTagManager::meta_template');
    }
}
