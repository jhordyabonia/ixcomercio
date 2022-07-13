<?php
/**
 * Aheadworks Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://ecommerce.aheadworks.com/end-user-license-agreement/
 *
 * @package    Popup
 * @version    1.2.3
 * @copyright  Copyright (c) 2020 Aheadworks Inc. (http://www.aheadworks.com)
 * @license    https://ecommerce.aheadworks.com/end-user-license-agreement/
 */
namespace Aheadworks\Popup\Model\Popup;

use Aheadworks\Popup\Model\Popup;
use Aheadworks\Popup\Model\PopupFactory;
use Aheadworks\Popup\Model\ResourceModel\Popup\Collection;
use Aheadworks\Popup\Model\Source\Event;
use Aheadworks\Popup\Model\Source\PageType;
use Aheadworks\Popup\Model\ThirdPartyModule\Manager;
use Aheadworks\Popup\Model\ResourceModel\Popup\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Aheadworks\Popup\Model\ThirdPartyModule\AwCustomerSegment\Resolver as AwCustomerSegmentResolver;
use Aheadworks\Popup\Model\Customer\Resolver as CustomerResolver;

/**
 * Class Provider
 * @package Aheadworks\Popup\Model\Popup
 */
class Provider
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $popupCollectionFactory;

    /**
     * @var Collection
     */
    private $popupCollection;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var PopupFactory
     */
    private $popupFactory;

    /**
     * @var AwCustomerSegmentResolver
     */
    private $awCustomerSegmentResolver;

    /**
     * @var CustomerResolver
     */
    private $customerResolver;

    /**
     * @param CollectionFactory $popupCollectionFactory
     * @param Manager $moduleManager
     * @param StoreManagerInterface $storeManager
     * @param CookieManagerInterface $cookieManager
     * @param Json $json
     * @param RequestInterface $request
     * @param PopupFactory $popupFactory
     * @param AwCustomerSegmentResolver $awCustomerSegmentResolver
     * @param CustomerResolver $customerResolver
     */
    public function __construct(
        CollectionFactory $popupCollectionFactory,
        Manager $moduleManager,
        StoreManagerInterface $storeManager,
        CookieManagerInterface $cookieManager,
        Json $json,
        RequestInterface $request,
        PopupFactory $popupFactory,
        AwCustomerSegmentResolver $awCustomerSegmentResolver,
        CustomerResolver $customerResolver
    ) {
        $this->moduleManager = $moduleManager;
        $this->storeManager = $storeManager;
        $this->popupCollectionFactory = $popupCollectionFactory;
        $this->cookieManager = $cookieManager;
        $this->json = $json;
        $this->request = $request;
        $this->popupFactory = $popupFactory;
        $this->awCustomerSegmentResolver = $awCustomerSegmentResolver;
        $this->customerResolver = $customerResolver;
    }

    /**
     * Get prepared popup collection
     *
     * @param int $blockType
     * @return Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPopupCollection($blockType)
    {
        if ($this->popupCollection === null) {
            /** @var Collection $popupCollection */
            $popupCollection = $this->popupCollectionFactory->create();
            $customerPageViewed = $this->getCustomerPageViewedCount();

            if ($this->moduleManager->isCustomerSegmentationModuleEnabled())
            {
                $popupCollection->addCustomerSegmentFilter(
                    $this->awCustomerSegmentResolver->getSegmentIdListForCurrentCustomer()
                );
            }

            $popupCollection
                ->addCustomerGroupFilter([$this->customerResolver->getGroupIdForCurrentCustomer()])
                ->addPageTypeFilter($blockType)
                ->addPageViewedFilter($customerPageViewed)
                ->addStoreFilter($this->storeManager->getStore()->getId())
                ->addStatusEnabledFilter();

            $this->popupCollection = $popupCollection;
        }

        return $this->popupCollection;
    }

    /**
     * If can show popup
     *
     * @param int $blockType
     * @param Popup $popup
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function canShow($blockType, $popup)
    {
        $result = true;
        $popupPageTypeArray = explode(',', $popup->getPageType());
        if ($blockType == PageType::PRODUCT_PAGE
            && in_array(PageType::PRODUCT_PAGE, $popupPageTypeArray)
        ) {
            $result = false;
            $currentProductId = $this->getProductId();
            if (null === $currentProductId) {
                return $result;
            }
            $popupModel = $this->popupFactory->create();
            $popupModel->load($popup->getId());
            $conditions = $popupModel->getRuleModel()->getConditions();
            if (isset($conditions)) {
                $match = $popupModel->getRuleModel()->getMatchingProductIds();
                if (in_array($currentProductId, $match)) {
                    $result = true;
                }
            }
        }

        if ($blockType == PageType::CATEGORY_PAGE
            && in_array(PageType::CATEGORY_PAGE, $popupPageTypeArray)
        ) {
            $result = false;
            $currentCategoryId = $this->getCurrentCategoryId();
            if ((!$popup->getCategoryIds())
                || ($currentCategoryId && in_array($currentCategoryId, explode(',', $popup->getCategoryIds())))
            ) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Get product id (used for popups in product page)
     *
     * @return string|null
     */
    public function getProductId()
    {
        return $this->request->getParam('id', null);
    }

    /**
     * Get category id (used for popups in category page)
     *
     * @return string|null
     */
    public function getCurrentCategoryId()
    {
        return $this->request->getParam('id', null);
    }

    /**
     * Get different viewed pages count
     *
     * @return int
     */
    private function getCustomerPageViewedCount()
    {
        $pageViewedJson = $this->cookieManager->getCookie(Event::VIEWED_PAGE_COUNT_COOKIE_NAME);
        $result = 0;
        if (null !== $pageViewedJson) {
            $pageViewedArray = $this->json->unserialize($pageViewedJson);
            $result = count($pageViewedArray);
        }
        return $result;
    }
}
