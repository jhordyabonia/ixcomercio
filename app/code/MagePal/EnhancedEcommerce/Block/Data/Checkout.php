<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Data;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote;
use MagePal\EnhancedEcommerce\Block\JsComponent;
use MagePal\EnhancedEcommerce\Helper\Data;
use MagePal\GoogleTagManager\DataLayer\QuoteData\QuoteItemProvider;
use MagePal\GoogleTagManager\Helper\DataLayerItem;

/**
 * Class Checkout
 * @package MagePal\EnhancedEcommerce\Block\Data
 */
class Checkout extends JsComponent
{
    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var QuoteItemProvider
     */
    protected $quoteItemProvider;

    /**
     * @var DataLayerItem
     */
    protected $dataLayerItemHelper;

    /**
     * @param Context|Context $context
     * @param Data $eeHelper
     * @param Session $checkoutSession
     * @param DataLayerItem $dataLayerItemHelper
     * @param QuoteItemProvider $quoteItemProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $eeHelper,
        Session $checkoutSession,
        DataLayerItem  $dataLayerItemHelper,
        QuoteItemProvider $quoteItemProvider,
        array $data = []
    ) {
        parent::__construct($context, $eeHelper, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->quoteItemProvider = $quoteItemProvider;
        $this->dataLayerItemHelper = $dataLayerItemHelper;
    }

    /**
     * Get active quote
     *
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Render information about specified orders and their items
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */

    public function getCart()
    {
        $quote = $this->getQuote();

        $items = [];

        if ($quote->getItemsCount()) {
            // set items
            foreach ($quote->getAllVisibleItems() as $item) {
                $object = $this->dataLayerItemHelper->getProductObject(
                    $item,
                    $item->getQty()
                );

                $items[] = $this->quoteItemProvider
                    ->setItemData($object)
                    ->setItem($item)
                    ->setActionType(QuoteItemProvider::ACTION_VIEW_CART)
                    ->setListType(QuoteItemProvider::LIST_TYPE_GOOGLE)
                    ->getData();
            }
        }

        return $items;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCartJson()
    {
        return json_encode($this->getCart());
    }

    /**
     * @return array
     */
    public function getCheckoutBehaviorSteps()
    {
        return [
          'shipping' => $this->_eeHelper->getCheckoutShippingIndex(),
          'payment' => $this->_eeHelper->getCheckoutPaymentIndex()
        ];
    }

    /**
     * @return string
     */
    public function getCheckoutBehaviorStepsJson()
    {
        return json_encode($this->getCheckoutBehaviorSteps());
    }
}
