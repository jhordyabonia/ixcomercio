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
        $order = $this->_checkoutSession->getQuote();
        $checkTerms  = array('terms'=>false);
        if($order->getCouponCode()!=''){
            $prefijoCupon = $scopeConfig->getValue('tradein/general/prefijo_cupon',ScopeInterface::SCOPE_STORE);
            $cupon = strpos($order->getCouponCode(), $prefijoCupon);
            if ($cupon !== false) {
                $enabled = $scopeConfig->getValue('tradein/general/terminos_condiciones',ScopeInterface::SCOPE_STORE);
                if($enabled){
                    $check = '<div class="checkout-agreements-block terms-tradein">
                    <div data-role="checkout-agreements">
                        <div class="checkout-agreements fieldset" data-bind="visible: isVisible">
                            <div class="checkout-agreement field choice required" novalidate="novalidate">
                                <input type="checkbox" class="required-entry checkbiling" data-bind="attr: {
                                                    \'id\': $parent.getCheckboxId($parentContext, agreementId),
                                                    \'name\': \'agreement[2]\',
                                                    \'value\': agreementId
                                                    }" id="agreement__2" name="agreement[2]" value="1" aria-invalid="true"
                                    aria-describedby="agreement[2]-error">
                                <label class="label" 
                                    for="agreement__2">
                                    <a href="'.$scopeConfig->getValue('tradein/general/enlace_redireccion',ScopeInterface::SCOPE_STORE).'" class="action action-show">
                                        <span >'.$scopeConfig->getValue('tradein/general/terminos_condiciones_label',ScopeInterface::SCOPE_STORE).'</span>
                                    </a>
                                </label>
                                <div for="agreement[2]" generated="true" class="mage-error" id="agreement[2]-error"
                                    style="display: none;">Campo obligatorio.</div>
                            </div>
                        </div>
                    </div>
                </div>';
                $checkTerms  = array(
                    'terms'=>true,
                    'check' => $check
                );
                }
            }
        }
        // Quitar
        $enabled = $scopeConfig->getValue('tradein/general/terminos_condiciones',ScopeInterface::SCOPE_STORE);
        if($enabled){
            $check = '<div class="checkout-agreements-block terms-tradein">
            <div data-role="checkout-agreements">
                <div class="checkout-agreements fieldset" data-bind="visible: isVisible">
                    <div class="checkout-agreement field choice required" novalidate="novalidate">
                        <input type="checkbox" class="required-entry checkbiling" data-bind="attr: {
                                            \'id\': $parent.getCheckboxId($parentContext, agreementId),
                                            \'name\': \'agreement[2]\',
                                            \'value\': agreementId
                                            }" id="agreement__2" name="agreement[2]" value="1" aria-invalid="true"
                            aria-describedby="agreement[2]-error">
                        <label class="label" 
                            for="agreement__2">
                            <a href="'.$scopeConfig->getValue('tradein/general/enlace_redireccion',ScopeInterface::SCOPE_STORE).'" class="action action-show">
                                <span >'.$scopeConfig->getValue('tradein/general/terminos_condiciones_label',ScopeInterface::SCOPE_STORE).'</span>
                            </a>
                        </label>
                        <div for="agreement[2]" generated="true" class="mage-error" id="agreement[2]-error"
                            style="display: none;">Campo obligatorio.</div>
                    </div>
                </div>
            </div>
        </div>';
            $checkTerms  = array(
                'terms'=>true,
                'check' => $check
            );
        }

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
        $fianalArray = array_merge($arrayData,$checkTerms);
        $resultJson->setData($fianalArray);
        return $resultJson;

    }

}