<?php
namespace Intcomex\Auditoria\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use \Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

class ObserverBeforeSaveProduct implements ObserverInterface
{
     /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected $_messageManager;

    private $helper;

    protected $_requestInterface;

    protected  $productRepository;

    public function __construct(
        ManagerInterface $messageManager,
        \Intcomex\Auditoria\Helper\Email $email,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Request\Http $request,
        StoreManagerInterface $storeManager,
        RequestInterface $requestInterface
        )
    {
        $this->_messageManager  = $messageManager;
        $this->helper = $email;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->_requestInterface = $requestInterface;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/observer_before_productsave.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $porcentaje = $this->scopeConfig->getValue('auditoria/general/porcentaje_validacion', $storeScope);
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        $products = $productFactory->create();

        $_product = $observer->getProduct();  // you will get product object
        $style = 'style="border:1px solid"';

        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $storeId = $this->storeManager->getStore()->getId();
        //$moduleName = $this->request->getModuleName();
        //$controller = $this->request->getControllerName();
        //$action     = $this->request->getActionName();
        //$route      = $this->request->getRouteName();
        $fullRequest = $this->_requestInterface->getFullActionName();
        $this->logger->info($fullRequest);
        if($fullRequest=='catalog_product_save'){
            $errors = '';
            $products->setStoreId($storeId);
            $productObj = $products->loadByAttribute('sku',trim($_product->getSku()));

            $price =  $_product->getPrice();
            if($price==''||empty($price)||$_product->getSpecialPrice()<$price){
                $price =  $_product->getSpecialPrice();
                $this->logger->info('Special price '.$websiteCode);
                $this->logger->info($price);
            }

            $precioReferencia = $productObj->getPrecioReferencia();

            $validar =  ($precioReferencia*(int)$porcentaje)/100;


            $this->logger->info('Se evalua '.$_product->getSku().' para '.$websiteCode);
            $this->logger->info('Precio a actualizar :'.$_product->getPrice());
            if($_product->getSpecialPrice()!=''&&!empty($_product->getSpecialPrice())){
                $this->logger->info('Precio a especial :'.$_product->getSpecialPrice());
            }
            $this->logger->info('Precio de referencia: '.$precioReferencia.' * '.$porcentaje.' / 100 = '.$validar);
            $this->logger->info(' ------- ');
            if($precioReferencia!=''&&!empty($precioReferencia)){
                if($price<$validar){
                    $errors .= '<tr>';
                    $errors .= '<td '.$style.' >'.$_product->getSku().'</td>';
                    $errors .= '<td '.$style.' >'.$websiteCode.'</td>';
                    $errors .= '<td '.$style.' >'.$price.'</td>';
                    $errors .= '<td '.$style.' >Precio</td>';
                    $errors .= '</tr>';
                }
            }else{
                $this->logger->info(' No se puede evaluar  '.$_product->getSku().' No tiene precio referencia en: '.$websiteCode);
            }
            if($errors!=''){
                $extraError = 'Estas ingresando valores no permitidos en el producto';
                $this->helper->notify('Soporte Whitelabel',$errors,$extraError, $storeId);
                throw new \Magento\Framework\Validator\Exception(__($extraError));
                // $this->_messageManager->addError(__("Error Message"));
            }
    
            return $this;
        }
       
    }
}