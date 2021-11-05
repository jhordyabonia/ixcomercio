<?php

namespace Intcomex\EventsObservers\Plugin\Api;

use Cdi\Custom\Helper\Api as CdiApi;
use Magento\Sales\Api\Data\OrderInterface;
use Trax\Ordenes\Model\ResourceModel\IwsOrder\CollectionFactory as IwsOrderCollectionFactory;

class OrderRepository
{
    /**
     * Order feedback field name
     */
    const BILLING_ADDRESS_IDENTIFICACTION = 'billing_address_identification';
    const TRANSACTION_VALUE_ID = 'transaction_value_id';
    const CUSTOMER_ID = 'trax_general/catalogo_retailer/customer_id';
    const MIENVIO_QUOTE_ID = 'mienvio_quote_id';
    const SHIPPING_ADDRESS_ZONE = 'shipping_address_zone';
    const KEYWORD_TO_FILTER_TRACKING_DATA_IN_ORDER_COMMENTS = 'guía';

    /**
     * @var CdiApi
     */
	protected $_cdiHelper;

    /**
     * @var IwsOrderCollectionFactory
     */
    protected $_iwsOrderCollectionFactory;

    /**
     * @param CdiApi $cdiHelper
     * @param IwsOrderCollectionFactory $iwsOrderCollectionFactory
     */
    public function __construct(
        CdiApi $cdiHelper,
        IwsOrderCollectionFactory $iwsOrderCollectionFactory
    ) {
        $this->_cdiHelper = $cdiHelper;
        $this->_iwsOrderCollectionFactory = $iwsOrderCollectionFactory;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/apiOrder.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param $entity
     * @return mixed
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject, 
        $entity
    ) {
        $configData = ['customer_id' => self::CUSTOMER_ID];
        $customer_id = $this->_cdiHelper->getConfigParams($configData,$entity->getStore()->getCode());
        $order_billing = $entity->getBillingAddress()->getData();
        $order_shipping = $entity->getShippingAddress()->getData();
        $order_payment = $entity->getPayment()->getData();
        $mienvioQuoteId = $entity->getMienvioQuoteId();

        $extensionAttributes = $entity->getExtensionAttributes();

        if ($extensionAttributes) {
            $extensionAttributes->setBillingAddressIdentification( $order_billing['identification'] );
            $extensionAttributes->setTransactionValueId( $order_payment['last_trans_id'] );
            $extensionAttributes->setMienvioQuoteId( $mienvioQuoteId );
            $extensionAttributes->setShippingAddressZone( $order_shipping['zone_id'] );

            $trackingData = $this->getTrackingData($entity);
            $extensionAttributes->setGuideNumber( $trackingData['guide_number'] );
            $extensionAttributes->setTrackingUrl( $trackingData['tracking_url'] );
            $extensionAttributes->setUrlPdfGuide( $trackingData['url_pdf_guide'] );
            $extensionAttributes->setCustomerId( $customer_id );

            $entity->setExtensionAttributes( $extensionAttributes );
        }

        return $entity;
    }

    /**
     * Add customer_feedback extension attribute to order data object to make it accessible in Magento API data
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject, 
        \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult
    ) {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {

            $configData = ['customer_id' => self::CUSTOMER_ID];
            $customer_id = $this->_cdiHelper->getConfigParams($configData,$order->getStore()->getCode());
            $order_billing = $order->getBillingAddress()->getData();
            $order_shipping = $order->getShippingAddress()->getData();
            $order_payment = $order->getPayment()->getData();
            $mienvioQuoteId = $order->getMienvioQuoteId();

            $extensionAttributes = $order->getExtensionAttributes();

            $extensionAttributes->setBillingAddressIdentification( $order_billing['identification'] );
            $extensionAttributes->setTransactionValueId( $order_payment['last_trans_id'] );
            $extensionAttributes->setMienvioQuoteId( $mienvioQuoteId );
            $extensionAttributes->setShippingAddressZone( $order_shipping['zone_id'] );

            $trackingData = $this->getTrackingData($order);
            $extensionAttributes->setGuideNumber( $trackingData['guide_number'] );
            $extensionAttributes->setTrackingUrl( $trackingData['tracking_url'] );
            $extensionAttributes->setUrlPdfGuide( $trackingData['url_pdf_guide'] );
            $extensionAttributes->setCustomerId( $customer_id );

            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }

    /**
     * Returns tracking data.
     *
     * @param OrderInterface $order
     * @return array
     */
    protected function getTrackingData(OrderInterface $order): array
    {
        $trackingData = [
            'guide_number'  => '',
            'tracking_url'  => '',
            'url_pdf_guide' => ''
        ];

        // Filter by comments related with tracking
        $comments = $order->getStatusHistoryCollection()
            ->addFieldToFilter(
                'comment',
                [
                    'like' => '% ' . self::KEYWORD_TO_FILTER_TRACKING_DATA_IN_ORDER_COMMENTS . ' %'
                ]
            )->getData();

        // Try to get tracking data from order comment's
        foreach ($comments as $comment){
            $explode = explode(PHP_EOL, $comment['comment']);
            if ($explode) {
                $realTrackingNumber = '';
                if (!empty($explode[2])) {
                    $stringGuideNumber = explode(' ', $explode[2]);
                    $realTrackingNumber = $stringGuideNumber ? end($stringGuideNumber) : '';
                }
                $trackingData['guide_number']  = $realTrackingNumber;
                $trackingData['tracking_url']  = (!empty($explode[5])) ? $explode[5]: '';
                $trackingData['url_pdf_guide'] = (!empty($explode[8])) ? $explode[8]: '';
            }
        }

        // If exists tracking_number in iws_order.mienvio_upload_resp sets this number
        $iwsOrder = $this->_iwsOrderCollectionFactory->create()
            ->addFieldToFilter('order_id', $order->getId())
            ->getFirstItem();
        $miEnvioResponse = unserialize($iwsOrder->getMienvioUploadResp());
        if (isset($miEnvioResponse['tracking_number'])) {
            $trackingData['guide_number'] = $miEnvioResponse['tracking_number'];
        }

        return $trackingData;
    }
}
