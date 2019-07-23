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

	const SANDBOX_MERCHANT_ID = 'payment/pasarela_bancomer/sandbox_merchant_id';

	const SANDBOX_LLAVE_SECRETA = 'payment/pasarela_bancomer/sandbox_sk';

    const SANDBOX_LLAVE_PUBLICA = 'payment/pasarela_bancomer/sandbox_pk';

	const PRODUCCION_MERCHANT_ID = 'payment/pasarela_bancomer/live_merchant_id';

	const PRODUCCION_LLAVE_SECRETA = 'payment/pasarela_bancomer/live_sk';

    const PRODUCCION_LLAVE_PUBLICA = 'payment/pasarela_bancomer/live_pk';
    
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
        
        $this->logger->debug('#AfterPlaceOrder', array('order_id' => $orderId[0], 'order_status' => $order->getStatus(), 'charge_id' => $charge->id, 'ext_order_id' => $order->getExtOrderId(), 'bancomer_status' => $charge->status));            

        echo 
        '<form id="bancomermultipagos-form" method="post" action="'.$configData['url'].'">
            <input type="hidden" name="mp_account" value="'.$configData['merchant_id'].'">
            <input type="hidden" name="mp_order" value="'.$orderId[0].'">
            <input type="hidden" name="mp_reference" value="'.$orderId[0].'">
            <input type="hidden" name="mp_product" value="1">
            <input type="hidden" name="mp_node" value="0">
            <input type="hidden" name="mp_concept" value="2">
            <input type="hidden" name="mp_amount" value="'.$order->getGrandTotal().'.00"><br>
            <input type="hidden" name="mp_currency" value="1"><br>
            <input type="hidden" name="mp_urlsuccess" value=" "><br>
            <input type="hidden" name="mp_urlfailure" value=" ">
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
        if($enviroment == '0'){
            $configData['url'] = $this->scopeConfig->getValue(self::URL_SANDBOX, $storeScope, $websiteCode);
            $configData['merchant_id'] = $this->scopeConfig->getValue(self::SANDBOX_MERCHANT_ID, $storeScope, $websiteCode);
            $configData['secret_key'] = $this->scopeConfig->getValue(self::SANDBOX_LLAVE_SECRETA, $storeScope, $websiteCode);
            $configData['public_key'] = $this->scopeConfig->getValue(self::SANDBOX_LLAVE_PUBLICA, $storeScope, $websiteCode);
        } else{
            $configData['url'] = $this->scopeConfig->getValue(self::URL_PRODUCCION, $storeScope, $websiteCode);
            $configData['merchant_id'] = $this->scopeConfig->getValue(self::PRODUCCION_MERCHANT_ID, $storeScope, $websiteCode);
            $configData['secret_key'] = $this->scopeConfig->getValue(self::PRODUCCION_LLAVE_SECRETA, $storeScope, $websiteCode);
            $configData['public_key'] = $this->scopeConfig->getValue(self::PRODUCCION_LLAVE_PUBLICA, $storeScope, $websiteCode);
        }
        return $configData;

    }

}
