<?php

namespace Intcomex\Credomatic\Controller\Custom;
use Magento\Store\Model\ScopeInterface;

class Storeconfig extends \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;

    protected $storeManager;

    protected $scopeConfig;

    protected $ruleMsi;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Intcomex\Credomatic\Model\RuleMsi $ruleMsi
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->ruleMsi = $ruleMsi;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        error_log('StoreConfig / excecute() ');
        $response = [];
        try {
            $configValue = $this->scopeConfig->getValue('payment/credomatic/CuotasOptions',ScopeInterface::SCOPE_STORE);
            error_log('Config Value: ' . print_r($configValue, true));

            $items = $this->_checkoutSession->getQuote()->getAllItems();
            foreach ($items as $item) {
                $product []= [
                    "sku" => $item->getSku(),
                    "qty" => $item->getQty()
                ];
            }
            $configValue = $this->ruleMsi->applyRule(
                $product,
                $configValue
            );
            $response = [
                'success' => true,
                'value' => __($configValue)
            ];

        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'value' => __($e->getMessage())
            ];
            $this->messageManager->addError($e->getMessage());
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

}