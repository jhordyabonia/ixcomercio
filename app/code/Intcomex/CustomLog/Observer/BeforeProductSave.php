<?php


namespace Intcomex\CustomLog\Observer;

use Magento\Framework\Event\ObserverInterface;


class BeforeProductSave implements ObserverInterface
{
    /**
     * @var \Zend\Log\Logger
     */
    protected $logger;

    /**
     * Http Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;


    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->request = $request;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/manual_price_change.log');

        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $this->authSession = $authSession;
    }


    /**
     *
     *  @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();

        if ((!empty($product))) {
            $productId = $product->getId();

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $oldproduct = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);

            $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $user_admin = $this->authSession->getUser();
            if(!$user_admin||empty($user_admin)||$user_admin==null){
                $userName = 'ssh';
                $userEmail = 'trax:upload --name stock';
            }else{
                $userName = $user_admin->getUsername();
                $userEmail = $user_admin->getEmail();
            }

            if ($oldproduct->getData('price') != $product->getData('price')):

                $this->logger->info('Manual Price - Product id: ' . $productId );
                $this->logger->info('Manual Price - Product sku: ' .  $product->getSku());
                $this->logger->info('Manual Price - Product old ' . $oldproduct->getData('price') );
                $this->logger->info('Manual Price - Product new ' . $product->getData('price') );


                $this->logger->info('Manual Price - User admin name: ' . $userName );
                $this->logger->info('Manual Price - User admin email: ' . $userEmail );

                $this->logger->info('Manual Price - Date: ' . date('Y-m-d G-i-s') );
                
                $product->setData('update_manual','1');
                $product->setData('update_file','0');
                $product->setData('update_cron','0');
            endif;
            
            $this->logger->info('getTypeId');
            $this->logger->info($product->getTypeId());
            if($product->getTypeId()!='configurable'){
                $errorsSku = array();
                $errors = '';
                $theme = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $websiteCode = $theme->getWebsite()->getCode();
    
                $style = 'style="border:1px solid"';
                $price = $product->getData('price');
                $special_price = $product->getData('special_price');
    
                $error = false;
                if($price==''||empty($price)||$price==0){
                    $error = true;
                }
                if($special_price!=$oldproduct->getData('special_price')){
                    if($special_price==''||empty($special_price)||$special_price==0){
                        $error = true;
                    }
                }
                if($websiteCode!='base'){
                   // $error = true;
                }
    
                if($error){
                    $errors .= '<tr>';
                    $errors .= '<td '.$style.' >'.$product->getSku().'</td>';
                    $errors .= '<td '.$style.' >'.$websiteCode.'</td>';
                    $errors .= '<td '.$style.' >'.$price.'</td>';
                    $errors .= '<td '.$style.' >'.$special_price.'</td>';
                    $errors .= '</tr>';
                }
    
                if($errors!=''){
                    $helper = $objectManager->get('\Intcomex\CustomLog\Helper\Email');
                    $templateId  = $scopeConfig->getValue('customlog/general/email_template');
                    $extraError = $scopeConfig->getValue('customlog/general/mensaje_alerta');
                    $email = explode(',',$scopeConfig->getValue('customlog/general/correos_alerta'));
    
                    $variables = array(
                        'mensaje' => $extraError,
                        'body' => $errors
                    );
                    foreach($email as $key => $value){
                        if(!empty($value)){
                            $helper->notify(trim($value),$variables,$templateId);
                        }
                    }
    
                    throw new \Magento\Framework\Validator\Exception(__($extraError));
                }
            }
            

        }
   
    }
}