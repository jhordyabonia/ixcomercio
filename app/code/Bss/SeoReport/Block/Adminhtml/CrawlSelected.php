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
namespace Bss\SeoReport\Block\Adminhtml;

use Bss\SeoReport\Helper\Data;
use Bss\SeoReport\Component\MassAction\Filter;
use Bss\SeoReport\Model\ResourceModel\ReportLinks\CollectionFactory;

class CrawlSelected extends \Magento\Backend\Block\Template
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Filter
     */
    public $filter;

    /**
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * CrawlSelected constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param Data $dataHelper
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        Data $dataHelper,
        Filter $filter,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|false|string
     */
    public function getJsonData()
    {

        /** @var \Bss\SeoReport\Model\ResourceModel\ReportLinks\Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $rewriteData = $collection->getData();
        $selectedItems = [];
        array_walk($rewriteData, function ($rewriteItemData) use (&$selectedItems) {
            if (isset($rewriteItemData['url_rewrite_id']) && $rewriteItemData['url_rewrite_id']) {
                $selectedItems[] = $rewriteItemData['url_rewrite_id'];
            }
        });
        $dataUnserialize = [
            'ajaxLink' => $this->getUrl('seo_report/crawl/getlinks'),
            'crawlLink' => $this->getUrl('seo_report/crawl/crawl'),
            'maxLink' => count($selectedItems),
            'totalLink' => count($selectedItems),
            'items' => empty($selectedItems) || !$selectedItems ? [] : $selectedItems
        ];
        return $this->dataHelper->jsonEncode($dataUnserialize);
    }

    /**
     * @return string
     */
    public function getDashboardLink()
    {
        return $this->getUrl('seo_report/dashboard/index');
    }
}
