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
use Pasarela\Bancomer\Model\Payment as Config;
use Magento\Framework\DataObject;

class AfterPlaceOrder implements ObserverInterface
{
    
    protected $config;
    protected $order;    
    protected $logger;    
    protected $_actionFlag;
    protected $_response;
    protected $_redirect;
    protected $openpayCustomerFactory;

    public function __construct(
        Config $config, 
        \Magento\Sales\Model\Order $order,        
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Psr\Log\LoggerInterface $logger_interface,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->config = $config;
        $this->order = $order;        
        $this->logger = $logger_interface;
        
        $this->_redirect = $redirect;
        $this->_response = $response;
        
        $this->_actionFlag = $actionFlag;                
    }
    
    public function execute(Observer $observer) {
        $orderId = $observer->getEvent()->getOrderIds();
        $order = $this->order->load($orderId[0]);                
                        
        if ($order->getPayment()->getMethod() != 'bancomer_multipagos') {
            return $this;
        }        
        
        $charge = $this->config->getBancomerCharge($order->getExtOrderId(), $order->getExtCustomerId());
        
        $this->logger->debug('#AfterPlaceOrder', array('order_id' => $orderId[0], 'order_status' => $order->getStatus(), 'charge_id' => $charge->id, 'ext_order_id' => $order->getExtOrderId(), 'bancomer_status' => $charge->status));                                    
        
        if ($charge->status == 'charge_pending' && isset($_SESSION['bancomer_3d_secure_url'])) {               
            $this->logger->debug('#AfterPlaceOrder', array('ext_order_id' => $order->getExtOrderId(), 'redirect_url' => $_SESSION['bancomer_3d_secure_url']));                    
            $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            $this->_redirect->redirect($this->_response, $_SESSION['bancomer_3d_secure_url']);            
        }         
    }    

}
