<?php

namespace Intcomex\TradeIn\Controller\Custom;
use Magento\Store\Model\ScopeInterface;

class TradeIn extends \Magento\Framework\App\Action\Action
{

    /**
    * @var \Magento\Framework\Controller\Result\JsonFactory
    */
    protected $resultJsonFactory;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        
        $resultJson = $this->resultJsonFactory->create();
        
        $post  = $this->getRequest()->getPostValue();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        $theme = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $mediaUrl = $theme->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
        $items = $this->_checkoutSession->getQuote()->getAllItems();
        
        $arrayData = array ('status' => 'error');
        
        foreach($items as $item) {
            $productObj = $productRepository->get($item->getSku());
            if(strtoupper($productObj->getData('iws_type'))=='TRADEIN'){
                $alerta1 = $scopeConfig->getValue('tradein/general/alerta_tradein_1',ScopeInterface::SCOPE_STORE);
                $alerta2 = $scopeConfig->getValue('tradein/general/alerta_tradein_2',ScopeInterface::SCOPE_STORE);
                $arrayData = array (
                    'status' => 'success',
                     'alerta1' => $alerta1,
                     'alerta2' => $alerta2,
                     'img' => $mediaUrl.'iconos_alerta/icono_'.$theme->getStore()->getCode().'.png'
                    );
                break;
            }    
            
        }
        $resultJson->setData($arrayData);
        return $resultJson;

    }

}