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
namespace Aheadworks\Popup\Block;

use Aheadworks\Popup\Model\Popup\Provider;
use Aheadworks\Popup\Model\ResourceModel\Popup\Collection;
use Aheadworks\Popup\Model\Source\PageType;
use Aheadworks\Popup\Model\Popup as PopupModel;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Class Popup
 * @package Aheadworks\Popup\Block
 */
class Popup extends Template implements IdentityInterface
{
    /**
     * Path to template file in theme.
     * @var string
     */
    protected $_template = 'Aheadworks_Popup::popup.phtml';

    /**
     * Block type
     *
     * @var int|null
     */
    private $blockType = null;

    /**
     * @var Provider
     */
    private $popupProvider;

    /**
     * @param Context $context
     * @param Provider $popupProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Provider $popupProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->popupProvider = $popupProvider;
    }

    /**
     * Get popup type
     *
     * @return int|null
     */
    public function getBlockType()
    {
        if ($this->blockType === null) {
            if (false !== strpos($this->getNameInLayout(), 'popup_product')) {
                $this->blockType = PageType::PRODUCT_PAGE;
            }
            if (false !== strpos($this->getNameInLayout(), 'popup_category')) {
                $this->blockType = PageType::CATEGORY_PAGE;
            }
            if (false !== strpos($this->getNameInLayout(), 'popup_cart')) {
                $this->blockType = PageType::SHOPPINGCART_PAGE;
            }
            if (false !== strpos($this->getNameInLayout(), 'popup_home')) {
                $this->blockType = PageType::HOME_PAGE;
            }
            if (false !== strpos($this->getNameInLayout(), 'popup_checkout')) {
                $this->blockType = PageType::CHECKOUT_PAGE;
            }
        }

        return $this->blockType;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getPopupCollection()->getItems() as $popup) {
            if ($this->getBlockType() == PageType::PRODUCT_PAGE
                && in_array(PageType::PRODUCT_PAGE, explode(',', $popup->getPageType()))
            ) {
                $identities[] = Product::CACHE_TAG . '_' . $this->popupProvider->getProductId();
            }
            if ($this->getBlockType() == PageType::CATEGORY_PAGE
                && in_array(PageType::CATEGORY_PAGE, explode(',', $popup->getPageType()))
            ) {
                $identities[] = Category::CACHE_TAG . '_' . $this->popupProvider->getCurrentCategoryId();
            }
            $identities[] = PopupModel::CACHE_TAG . '_' . $popup->getId();
        }

        return $identities;
    }

    /**
     * Get popup collection
     *
     * @return Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getPopupCollection()
    {
        $blockType = $this->getBlockType();

        return $this->popupProvider->getPopupCollection($blockType);
    }
}
