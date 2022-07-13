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
namespace Aheadworks\Popup\Controller\Ajax;

use Aheadworks\Popup\Model\Source\Event;

/**
 * Class AddCookie
 * @package Aheadworks\Popup\Controller\Ajax
 */
class AddCookie extends \Aheadworks\Popup\Controller\Ajax
{
    const DEFAULT_COOKIE_LIFETIME = 86400;

    /**
     * Popup model factory
     * @var \Aheadworks\Popup\Model\PopupFactory
     */
    private $popupModelFactory;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Aheadworks\Popup\Model\PopupFactory $popupModelFactory
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Aheadworks\Popup\Model\PopupFactory $popupModelFactory
    ) {
        parent::__construct(
            $customerSession,
            $formKeyValidator,
            $context
        );
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->popupModelFactory = $popupModelFactory;
    }

    /**
     * Add public cookie
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $lifetime = $this->getRequest()->getParam('cookie_lifetime', self::DEFAULT_COOKIE_LIFETIME);
        $type = $this->getRequest()->getParam('cookie_type');
        switch ($type) {
            case Event::VIEWED_POPUP_COUNT_COOKIE_NAME:
                $resultValue = $this->getRequest()->getParam('popup_id', null);
                $name = $type . '_' . $resultValue;
                $value = $resultValue;
                $this->addViewToPopup($resultValue);
                break;
            case Event::VIEWED_PAGE_COUNT_COOKIE_NAME:
                $resultValue = $this->getRequest()->getParam('current_url', '');
                $name = $type;
                $value = $this->getPageCountArrayValue();
                $value[] = hash('sha256', $resultValue);
                $value = json_encode(array_unique($value));
                break;
            case Event::USED_POPUP_COUNT_COOKIE_NAME:
                $resultValue = $this->getRequest()->getParam('popup_id', null);
                $name = $type . '_' . $resultValue;
                $value = $resultValue;
                if (false !== $this->isUsedPopup($name)) {
                    $resultValue = null;
                } else {
                    $this->addClickToPopup($resultValue);
                }
                break;
            default:
                $resultValue = $this->getRequest()->getParam('current_url', '');
                $name = $type;
                $value = $this->getPageCountArrayValue();
                $value[] = hash('sha256', $resultValue);
                $value = json_encode(array_unique($value));
                break;
        }

        if ($resultValue) {
            $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setDuration($lifetime)
                ->setPath($this->customerSession->getCookiePath())
                ->setDomain($this->customerSession->getCookieDomain())
                ->setSecure(false)
                ->setHttpOnly(false);

            $this->cookieManager->setPublicCookie($name, $value, $cookieMetadata);
        }
    }

    /**
     * Get viewed pages (private)
     *
     * @return array|mixed
     */
    private function getPageCountArrayValue()
    {
        $cookieValue = $this->cookieManager->getCookie(
            Event::VIEWED_PAGE_COUNT_COOKIE_NAME
        );
        if (null !== $cookieValue) {
            $cookieArray = json_decode($cookieValue);
            if (strlen($cookieValue) > 2000) {
                array_shift($cookieArray);
            }
            return $cookieArray;
        } else {
            return [];
        }
    }

    /**
     * Check if used popup (private)
     *
     * @param string $cookieName
     * @return bool
     */
    private function isUsedPopup($cookieName)
    {
        $cookieValue = $this->cookieManager->getCookie($cookieName);
        if (null !== $cookieValue) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Increase popup clicks (private)
     *
     * @param int $popupId
     * @return $this
     */
    private function addClickToPopup($popupId)
    {
        $popupModel = $this->popupModelFactory->create();
        $popupModel->load($popupId);
        if ($popupModel->getId()) {
            $popupModel->setClickCount($popupModel->getClickCount() + 1);
            $popupModel->save();
        }
        return $this;
    }

    /**
     * Increase popup views (private)
     *
     * @param int $popupId
     * @return $this
     */
    private function addViewToPopup($popupId)
    {
        $popupModel = $this->popupModelFactory->create();
        $popupModel->load($popupId);
        if ($popupModel->getId()) {
            $popupModel->setViewCount($popupModel->getViewCount() + 1);
            $popupModel->save();
        }
        return $this;
    }
}
