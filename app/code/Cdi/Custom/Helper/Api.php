<?php
namespace Cdi\Custom\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;

class Api extends AbstractHelper{
 
	protected $_scopeConfig;
	protected $_storeManager;
	protected $_objectManager;

	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	){
		$this->_scopeConfig = $scopeConfig;
		$this->_storeManager = $storeManager;	
	}

	//Obtiene los parámetros de configuración desde el cms
	public function getConfigParams($fields)
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$websiteCode = $this->_storeManager->getStore()->getCode();
		$configData = array();
		foreach($fields as $key => $path){
			$configData[$key] = $this->_scopeConfig->getValue($path, $storeScope, $websiteCode);
		}
		return $configData;
	}

	/*Retorna objectManager vacío, o una clase construida*/
	public function getObjectManager($class = null){
		if(is_null($this->_objectManager)){
			$this->_objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
		}
		if(is_null($class)){
			return $this->_objectManager;
		}
		//'\Magento\Store\Model\StoreManagerInterface'
		return $this->_objectManager->get($class);
	}

	//Se añade comentario interno a orden
    public function addOrderComment($order, $comment, $notify = false, $status = false) 
    {		
        $status = ($status) ? $status : $order->getStatus();
		try {
            $history = $order->addStatusHistoryComment($comment, $status);
            $history->setIsVisibleOnFront(false);
            $history->setIsCustomerNotified($notify);
            $history->save();
            $order->save();
			$orderCommentSender = $this->getObjectManager(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class);
			$orderCommentSender->send($order, $notify, $comment);
        }catch(\Exception $e){
            $this->logger->info('Error al guardar comentario en orden con ID: '.$order->getEntityId());
        }
	}
}