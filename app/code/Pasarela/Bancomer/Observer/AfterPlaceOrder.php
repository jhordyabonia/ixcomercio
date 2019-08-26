<?php
/** 
 * @category    Payments
 * @package     Pasarela_Bancomer
 * @author      Valentina Aguirre
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
 */

namespace Pasarela\Bancomer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\DataObject;

class AfterPlaceOrder implements ObserverInterface
{

    const SANDBOX = 'payment/pasarela_bancomer/is_sandbox';

	const URL_SANDBOX = 'payment/pasarela_bancomer/url_sandbox_bancomer';

	const URL_PRODUCCION = 'payment/pasarela_bancomer/url_produccion_bancomer';

	const SANDBOX_MP_ACCOUNT = 'payment/pasarela_bancomer/sandbox_mp_account';

	const SANDBOX_MP_NODE = 'payment/pasarela_bancomer/sandbox_mp_node';

    const SANDBOX_MP_CONCEPT = 'payment/pasarela_bancomer/sandbox_mp_concept';

    const SANDBOX_PRIVATE_KEY = 'payment/pasarela_bancomer/sandbox_private_key';

	const PRODUCCION_MP_ACCOUNT = 'payment/pasarela_bancomer/live_mp_account';

	const PRODUCCION_MP_NODE = 'payment/pasarela_bancomer/live_mp_node';

    const PRODUCCION_MP_CONCEPT = 'payment/pasarela_bancomer/live_mp_concept';

    const PRODUCCION_PRIVATE_KEY = 'payment/pasarela_bancomer/live_private_key';
    
    protected $config;
    protected $order;    
    protected $logger;    
    protected $_actionFlag;
    protected $_response;
    protected $_redirect;
    protected $openpayCustomerFactory;
	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Sales\Model\Order $order,        
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Psr\Log\LoggerInterface $logger_interface,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->order = $order;        
        $this->logger = $logger_interface;
        
        $this->_redirect = $redirect;
        $this->_response = $response;
        
        $this->_actionFlag = $actionFlag;     
        $this->scopeConfig = $scopeConfig;           
    }
    
    public function execute(Observer $observer) {
        $orderId = $observer->getEvent()->getOrderIds();
        $order = $this->order->load($orderId[0]);                
                        
        if ($order->getPayment()->getMethod() != 'pasarela_bancomer') {
            return $this;
        }       

		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();     
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		//Se obtienen parametros de configuración por Store
		$configData = $this->getConfigParams($storeScope, $storeManager->getStore()->getCode());
        
        $this->logger->debug('#AfterPlaceOrder', array('order_id' => $orderId[0], 'order_status' => $order->getStatus()));           
        $billing = $order->getBillingAddress(); 
        $cadena = $orderId[0].$order->getIncrementId().sprintf("%.2f", $order->getGrandTotal());
        echo 
        '<form id="bancomermultipagos-form" method="post" action="'.$configData['url'].'">
            <input type="hidden" name="mp_account" value="'.$configData['mp_account'].'">
            <input type="hidden" name="mp_product" value="1">
            <input type="hidden" name="mp_order" value="'.$orderId[0].'">
            <input type="hidden" name="mp_reference" value="'.$order->getIncrementId().'">
            <input type="hidden" name="mp_node" value="'.$configData['mp_node'].'">
            <input type="hidden" name="mp_concept" value="'.$configData['mp_concept'].'">
            <input type="hidden" name="mp_amount" value="'.sprintf("%.2f", $order->getGrandTotal()).'">
            <input type="hidden" name="mp_customername" value="'.$billing->getFirstname().' '.$billing->getLastname().'">
            <input type="hidden" name="mp_email" value="'.$billing->getEmail().'">
            <input type="hidden" name="mp_phone" value="'.$billing->getTelephone().'">
            <input type="hidden" name="mp_currency" value="1">
            <input type="hidden" name="mp_signature" value="'.hash_hmac('sha256', $cadena, $configData['private_key']).'">
            <input type="hidden" name="mp_urlsuccess" value="'.$storeManager->getStore()->getBaseUrl().'bancomer/payment/success">
            <input type="hidden" name="mp_urlfailure" value="'.$storeManager->getStore()->getBaseUrl().'bancomer/payment/error">
        </form>
        <script type="text/javascript">
            document.getElementById("bancomermultipagos-form").submit();
        </script>';
    }    

    //Obtiene los parámetros de configuración desde el cms
    public function getConfigParams($storeScope, $websiteCode) 
    {
        $enviroment = $this->scopeConfig->getValue(self::SANDBOX, $storeScope, $websiteCode);
        //Se valida entorno para obtener url del servicio
        if($enviroment == '1'){
            $configData['url'] = $this->scopeConfig->getValue(self::URL_SANDBOX, $storeScope, $websiteCode);
            $configData['mp_account'] = $this->scopeConfig->getValue(self::SANDBOX_MP_ACCOUNT, $storeScope, $websiteCode);
            $configData['mp_node'] = $this->scopeConfig->getValue(self::SANDBOX_MP_NODE, $storeScope, $websiteCode);
            $configData['mp_concept'] = $this->scopeConfig->getValue(self::SANDBOX_MP_CONCEPT, $storeScope, $websiteCode);
            $configData['private_key'] = $this->scopeConfig->getValue(self::SANDBOX_PRIVATE_KEY, $storeScope, $websiteCode);
        } else{
            $configData['url'] = $this->scopeConfig->getValue(self::URL_PRODUCCION, $storeScope, $websiteCode);
            $configData['mp_account'] = $this->scopeConfig->getValue(self::PRODUCCION_MP_ACCOUNT, $storeScope, $websiteCode);
            $configData['mp_node'] = $this->scopeConfig->getValue(self::PRODUCCION_MP_NODE, $storeScope, $websiteCode);
            $configData['mp_concept'] = $this->scopeConfig->getValue(self::PRODUCCION_MP_CONCEPT, $storeScope, $websiteCode);
            $configData['private_key'] = $this->scopeConfig->getValue(self::PRODUCCION_PRIVATE_KEY, $storeScope, $websiteCode);
        }
        return $configData;

    }

}
