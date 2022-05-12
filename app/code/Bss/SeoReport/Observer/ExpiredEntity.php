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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Observer;

use Bss\SeoReport\Helper\SeoReportHelper;
use Magento\Framework\App\RequestInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory as UrlRewriteCollectionFactory;

class ExpiredEntity
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SeoReportHelper
     */
    protected $seoReportHelper;

    /**
     * @var UrlRewriteCollectionFactory
     */
    protected $urlRewriteCollectionFactory;

    /**
     * ExpiredEntity constructor.
     * @param RequestInterface $request
     * @param SeoReportHelper $seoReportHelper
     * @param UrlRewriteCollectionFactory $urlRewriteCollectionFactory
     */
    public function __construct(
        RequestInterface $request,
        SeoReportHelper $seoReportHelper,
        UrlRewriteCollectionFactory $urlRewriteCollectionFactory
    ) {
        $this->request = $request;
        $this->seoReportHelper = $seoReportHelper;
        $this->urlRewriteCollectionFactory = $urlRewriteCollectionFactory;
    }

    /**
     * @param $entityId
     * @param $entityType
     * @return bool
     */
    public function setExpiredEntity($entityId, $entityType)
    {
        $currentStore = $this->request->getParam('store');
        /** @var UrlRewriteCollection $urlRewriteCollection */
        $urlRewriteCollection = $this->urlRewriteCollectionFactory->create();
        $urlRewriteCollection->addFieldToFilter('entity_id', $entityId)
            ->addFieldToFilter('entity_type', $entityType);

        if ($currentStore) {
            $urlRewriteCollection->addFieldToFilter('store_id', $currentStore);
        }
        $urlRewriteCollection->addFieldToSelect('url_rewrite_id');
        $items = $urlRewriteCollection->getItems();
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $item->getData('url_rewrite_id');
            $dataInsert = [
                'url_rewrite_id' => $item->getData('url_rewrite_id'),
                'expired' => time() - 86400
            ];
            $this->seoReportHelper->handleData($dataInsert);
        }
        return true;
    }
}
