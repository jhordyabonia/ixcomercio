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
namespace Aheadworks\Popup\ViewModel\Popup;

use Aheadworks\Popup\Model\Config;
use Aheadworks\Popup\Model\Popup as PopupModel;
use Aheadworks\Popup\Model\Popup\Provider;
use Aheadworks\Popup\Model\UserAgent\Matcher\Bot as BotMatcher;
use Aheadworks\Popup\Model\UserAgent\Matcher\Mobile as MobileMatcher;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class DataProvider
 * @package Aheadworks\Popup\ViewModel\Popup
 */
class DataProvider implements ArgumentInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var FilterProvider
     */
    private $templateFilterProvider;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Provider
     */
    private $popupProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Base64Json
     */
    private $base64Json;

    /**
     * @var MobileMatcher
     */
    private $mobileMatcher;

    /**
     * @var BotMatcher
     */
    private $botMatcher;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param FilterProvider $templateFilterProvider
     * @param Json $json
     * @param Provider $popupProvider
     * @param RequestInterface $request
     * @param Base64Json $base64Json
     * @param FormKey $formKey
     * @param MobileMatcher $mobileMatcher
     * @param BotMatcher $botMatcher
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Config $config,
        FilterProvider $templateFilterProvider,
        Json $json,
        Provider $popupProvider,
        RequestInterface $request,
        Base64Json $base64Json,
        FormKey $formKey,
        MobileMatcher $mobileMatcher,
        BotMatcher $botMatcher
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->json = $json;
        $this->templateFilterProvider = $templateFilterProvider;
        $this->formKey = $formKey;
        $this->popupProvider = $popupProvider;
        $this->request = $request;
        $this->base64Json = $base64Json;
        $this->mobileMatcher = $mobileMatcher;
        $this->botMatcher = $botMatcher;
    }

    /**
     * Check if use https
     *
     * @return boolean
     * @throws NoSuchEntityException
     */
    public function isSecure()
    {
        return $this->storeManager->getStore()->isCurrentlySecure();
    }

    /**
     * Prepare popups in JSON format
     *
     * @param int $blockType
     * @return false|string
     * @throws NoSuchEntityException
     */
    public function prepareJsonPopups($blockType)
    {
        return $this->json->serialize($this->getPopupsContentHtml($blockType));
    }

    /**
     * Get prepared form key
     *
     * @return bool|false|string
     * @throws LocalizedException
     */
    public function getPreparedFormKey()
    {
        return $this->json->serialize($this->getFormKey());
    }

    /**
     * Get form key
     *
     * @return string
     * @throws LocalizedException
     */
    private function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Prepare popup info object from request
     *
     * @return DataObject
     */
    private function preparePopupInfo()
    {
        return new DataObject(
            $this->base64Json->unserialize($this->request->getParam('popup_info', ''))
        );
    }

    /**
     * Retrieve Popups content
     *
     * @param int $blockType
     * @return array
     * @throws NoSuchEntityException
     */
    private function getPopupsContentHtml($blockType)
    {
        if (!$this->config->getHidePopupForMobileDevices() && !$this->config->getHidePopupForSearchEngines()) {
            return $this->getPopupsArrayContentHtml($blockType);
        }

        $userAgent = $this->request->getHeader('User-Agent');
        $server = $this->request->getServer();
        $isMobile = $this->mobileMatcher->match($userAgent, $server);
        $isBot = $this->botMatcher->match($userAgent, $server);
        $result = [];

        if (($this->config->getHidePopupForMobileDevices() && $isMobile)
            || ($this->config->getHidePopupForSearchEngines() && $isBot)) {
            return $result;
        } else {
            return $this->getPopupsArrayContentHtml($blockType);
        }
    }

    /**
     * Get popups for current page
     *
     * @param int $blockType
     * @return array
     * @throws NoSuchEntityException
     */
    private function getPopupsArrayContentHtml($blockType)
    {
        $rFrom = [
            '"' . ActionInterface::PARAM_NAME_URL_ENCODED . '":',
            "checkout\/cart\/add\/" . ActionInterface::PARAM_NAME_URL_ENCODED . "\/",
            "checkout/cart/add/" . ActionInterface::PARAM_NAME_URL_ENCODED . "/",
            'name="' . ActionInterface::PARAM_NAME_URL_ENCODED . '"'
        ];
        $rTo = [
            '"' . ActionInterface::PARAM_NAME_URL_ENCODED . 'disable":',
            "checkout\/cart\/add\/".ActionInterface::PARAM_NAME_URL_ENCODED . "disable\/",
            "checkout/cart/add/" . ActionInterface::PARAM_NAME_URL_ENCODED . "disable/",
            'name="' . ActionInterface::PARAM_NAME_URL_ENCODED . 'disable"'
        ];

        $templateFilter = $this->templateFilterProvider->getBlockFilter()
            ->setStoreId($this->storeManager->getStore()->getId());

        /* prepare popup from backend for preview */
        if ($this->request->getParam('preview', 0)) {
            $popupsContentArr = [];
            $popupInfo = $this->preparePopupInfo();
            $id = $popupInfo->getData('popupId');
            $popupsContentArr[$id]['preview'] = true;
            $popupsContentArr[$id]['effect'] = $popupInfo->getEffect();
            $popupsContentArr[$id]['position'] = $popupInfo->getPosition();
            $popupsContentArr[$id]['content'] =
                '<div class="popup-content mfp-with-anim">' .
                str_replace($rFrom, $rTo, $templateFilter->filter($popupInfo->getContent())) .
                '<style type="text/css">' . $popupInfo->getCustomCss() . '</style></div>';

            return $popupsContentArr;
        }

        try {
            $popupCollection = $this->popupProvider->getPopupCollection($blockType);
        } catch (\Exception $e) {
            return [];
        }
        $popupsContentArr = [];
        /** @var PopupModel $popup */
        foreach ($popupCollection->getItems() as $popup) {
            if ($this->popupProvider->canShow($blockType, $popup)) {
                $popupsContentArr[$popup->getId()]['effect'] = $popup->getEffect();
                $popupsContentArr[$popup->getId()]['event'] = $popup->getEvent();
                $popupsContentArr[$popup->getId()]['position'] = $popup->getPosition();
                $popupsContentArr[$popup->getId()]['event_value'] = $popup->getEventValue();
                $popupsContentArr[$popup->getId()]['content'] =
                    '<div class="popup-content mfp-with-anim">' .
                    str_replace($rFrom, $rTo, $templateFilter->filter($popup->getContent())) .
                    '<style type="text/css">' . $popup->getCustomCss() . '</style></div>'
                ;
                $popupsContentArr[$popup->getId()]['lifetime'] = $popup->getCookieLifetime() * 60;
            }
        }

        return $popupsContentArr;
    }
}
