<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Model\Data;

use Wagento\Zendesk\Api\Data\CustomerOrderInterface;

/**
 * Class CustomerOrder
 * @package Wagento\Zendesk\Model\Data
 */
class CustomerOrder extends \Magento\Framework\Api\AbstractExtensibleObject implements CustomerOrderInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;
    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    private $addressRenderer;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CustomerOrder constructor.
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $attributeValueFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $attributeValueFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
    
        parent::__construct($extensionFactory, $attributeValueFactory, $data);
        $this->orderFactory = $orderFactory;
        $this->addressRenderer = $addressRenderer;
        $this->pricingHelper = $pricingHelper;
        $this->localeDate = $localeDate;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get customer email
     * @return string
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * Get customer firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->_get(self::FIRSTNAME);
    }

    /**
     * Get customer lastname
     * @return string
     */
    public function getLastname()
    {
        return $this->_get(self::LASTNAME);
    }

    /**
     * @return \Wagento\Zendesk\Api\Data\OrderReaderInterface[] Array of ord`ers.
     */
    public function getOrders()
    {
        if ($this->_get(self::ORDERS) == null) {
            $orders = $this->getOrderCollection();
            $this->setData(
                self::ORDERS,
                $orders
            );
        }
        return $this->_get(self::ORDERS);
    }

    /**
     * Set email address
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    /**
     * Sets orders for the customer.
     *
     * @param \Wagento\Zendesk\Api\Data\OrderReaderInterface[] $orders
     * @return $this
     */
    public function setOrder($orders)
    {
        return $this->setData(self::ORDERS, $orders);
    }

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created at time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get customer group
     * @return string
     */
    public function getGroup()
    {
        return $this->_get(self::GROUP);
    }

    /**
     * Set group
     *
     * @param string $group
     * @return $this
     */
    public function setGroup($group)
    {
        return $this->setData(self::GROUP, $group);
    }

    /**
     * Get lifetime sales for the customer.
     *
     * @return float|null
     */
    public function getLifetimeSales()
    {
        return $this->_get(self::LIFETIME_SALES);
    }

    /**
     * Set lifetime sales for the customer.
     *
     * @param float $lifetimeSales
     * @return $this
     */
    public function setLifetimeSales($lifetimeSales)
    {
        return $this->setData(self::LIFETIME_SALES, $lifetimeSales);
    }

    /**
     * @return \Wagento\Zendesk\Api\Data\OrderReaderInterface[]
     */
    private function getOrderCollection()
    {
        $email = $this->getEmail();

        $orderInstance = $this->orderFactory->create();
        $orderCollection = $orderInstance->getCollection();

        // Order Limit
        $orderLimit = $this->scopeConfig->getValue('zendesk/zendesk_m2app/order_qty_limit');
        if (isset($orderLimit) && is_numeric($orderLimit)) {
            $orderCollection->setPageSize($orderLimit);
        }
        //Load rest of information
        $orderCollection->addFieldToSelect('entity_id')
            ->addFieldToFilter('customer_email', $email)
            ->setOrder('entity_id', 'DESC')
            ->load();

        $orders = [];
        foreach ($orderCollection as $order) {
            $orders[] = $this->getOrderData($order->getId());
        }

        return $orders;
    }

    /**
     * Retrieve order information
     *
     * @param $id
     * @return array
     */
    private function getOrderData($id)
    {
        $orderInstance = $this->orderFactory->create();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $orderInstance->load($id);

        /** @var \Magento\Sales\Model\Order\Address $billing */
        $billing = $order->getBillingAddress();
        $billing_address = '-';
        if ($billing) {
            $billing_address = $this->addressRenderer->format($billing, 'html');
        }

        /** @var \Magento\Sales\Model\Order\Address $shipping */
        $shipping = $order->getShippingAddress();
        $shipping_address = '-';
        if ($shipping) {
            $shipping_address = $this->addressRenderer->format($shipping, 'html');
        }

        $createdAt = $order->getCreatedAt();

        $formattedCreatedAt = $this->formatDate($createdAt, \IntlDateFormatter::MEDIUM) . ', ' . $this->formatTime($createdAt);
        $storeName = $this->storeManager->getStore($order->getStoreId())->getWebsite()->getName();

        $paymentDescription = $order->getPayment()->getMethodInstance()->getTitle();
        $paymentMethod = $paymentDescription ? $paymentDescription : '-';

        $shippingDescription = $order->getShippingDescription();
        $shippingMethod = $shippingDescription ? $shippingDescription : '-';

        $orderInfo = [
            'increment_id' => $order->getIncrementId(),
            'created_at' => $formattedCreatedAt,
            'status' => $order->getStatus(),
            'store_name' => $storeName,
            'billing_address' => $billing_address,
            'shipping_address' => $shipping_address,
            'subtotal' => $this->formatPrice($order->getSubtotal()),
            'shipping_amount' => $this->formatPrice($order->getShippingAmount()),
            'discount_amount' => $this->formatPrice($order->getDiscountAmount()),
            'tax_amount' => $this->formatPrice($order->getTaxAmount()),
            'grand_total' => $this->formatPrice($order->getGrandTotal()),
            'total_paid' => $this->formatPrice($order->getTotalPaid()),
            'total_refunded' => $this->formatPrice($order->getTotalRefunded()),
            'total_due' => $this->formatPrice($order->getTotalDue()),
            'payment_method' => $paymentMethod,
            'shipping_method' => $shippingMethod,
            'items' => []
        ];

        foreach ($order->getItems() as $item) {
            // original float values
            $originalPrice = $item->getOriginalPrice();
            $price = $item->getPrice();
            $qtyOrdered = $item->getQtyOrdered() * 1;
            $subtotal = $qtyOrdered * $price;
            $taxAmount = $item->getTaxAmount();
            $taxPercent = $item->getTaxPercent() * 1;
            $discountAmount = $item->getDiscountAmount();
            $rowTotal = $item->getRowTotal() - $discountAmount;

            $itemInfo['name'] = $item->getName();
            $itemInfo['sku'] = $item->getSku();
            $itemInfo['status'] = $item->getStatus();
            $itemInfo['original_price'] = $this->formatPrice($originalPrice);
            $itemInfo['price'] = $this->formatPrice($price);
            $itemInfo['qty_ordered'] = $qtyOrdered;
            $itemInfo['subtotal'] = $this->formatPrice($subtotal);
            $itemInfo['tax_amount'] = $this->formatPrice($taxAmount);
            $itemInfo['tax_percent'] = $taxPercent;
            $itemInfo['discount'] = $this->formatPrice($discountAmount);
            $itemInfo['total'] = $this->formatPrice($rowTotal);
            $orderInfo['items'][] = $itemInfo;
        }

        return $orderInfo;
    }

    /**
     * Retrieve formatting price
     *
     * @param $price
     * @return float|string
     */
    public function formatPrice($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    /**
     * Retrieve formatting date
     *
     * @param null|string|\DateTimeInterface $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
    
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * Retrieve formatting time
     *
     * @param   \DateTime|string|null $time
     * @param   int $format
     * @param   bool $showDate
     * @return  string
     */
    public function formatTime(
        $time = null,
        $format = \IntlDateFormatter::SHORT,
        $showDate = false
    ) {
    
        $time = $time instanceof \DateTimeInterface ? $time : new \DateTime($time);
        return $this->localeDate->formatDateTime(
            $time,
            $showDate ? $format : \IntlDateFormatter::NONE,
            $format
        );
    }
}
