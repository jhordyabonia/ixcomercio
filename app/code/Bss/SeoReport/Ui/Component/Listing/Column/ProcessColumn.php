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
namespace Bss\SeoReport\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Price
 */
class ProcessColumn extends Column
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\SeoReport\Helper\ItemProcess
     */
    private $itemProcess;

    /**
     * ProcessColumn constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\SeoReport\Helper\ItemProcess $itemProcess
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\SeoReport\Helper\ItemProcess $itemProcess,
        array $components = [],
        array $data = []
    ) {
        $this->itemProcess = $itemProcess;
        $this->storeManager = $storeManager;
        $this->context = $context;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $baseUrlObject = [];
            foreach ($dataSource['data']['items'] as & $item) {
                $storeId = $item['store_id'];
                if (!isset($baseUrlObject[$storeId])) {
                    $baseUrlObject[$storeId] = $this->getStoreUrl($storeId);
                }
                $baseUrl = $baseUrlObject[$storeId];
                $urlPath = ltrim($item['request_path'], "/");
                $item['request_path'] = $baseUrl . $urlPath;

                $entityId = $item['entity_id'];
                $entityType =  $item['entity_type'];
                $backendUrl = $this->getEntityUrl($entityType, $entityId);

                $item = $this->itemProcess->processEntityMeta($item, $entityType, $entityId);
                $item = $this->itemProcess->processEntityTag($item);
                $item = $this->itemProcess->processEntityImages($item);
                $item = $this->itemProcess->processHeadings($item);

                $item['url_action'] = $this->getUrlAction($item['status'], $backendUrl, $item['request_path']);
                $item['status'] = isset($item['expired']) ? $this->getExpiredStatus($item['expired']) : true;
            }
        }
        return $dataSource;
    }

    /**
     * @param string $entityType
     * @param int|string $entityId
     * @return string
     */
    protected function getEntityUrl($entityType, $entityId)
    {
        if ($entityType === 'product' || $entityType === 'category') {
            return $this->context->getUrl('catalog/' . $entityType . '/edit/id/' . $entityId);
        }
        return $this->context->getUrl('cms/page/edit/page_id/' . $entityId);
    }

    /**
     * @param bool|string|int $status
     * @param string $backendUrl
     * @param string $requestPath
     * @return string
     */
    protected function getUrlAction($status, $backendUrl, $requestPath)
    {
        if ((int)$status === 1) {
            return '<a href="' . $backendUrl . '" target="_blank">' . __('Edit') . '</a> | <a href="' .
                $requestPath . '" target="_blank">' . __('View') . '</a>';
        }
        return '<a href="' . $backendUrl . '" target="_blank">' . __('Edit') . '</a>';
    }

    /**
     * @param int $expiredTime
     * @return bool
     */
    protected function getExpiredStatus($expiredTime)
    {
        if (time() - $expiredTime > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreUrl($storeId)
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl();
    }
}
