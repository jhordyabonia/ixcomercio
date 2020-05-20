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
		echo "<pre>";
		var_dump($configData);
		die();
		return $configData;
	}

	/*Retorna objectManager vacío, o una clase construida*/
	public function getObjectManager($class = ''){
		if(is_null($this->_objectManager)){
			$this->_objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
		}
		if($class == ''){
			return $this->_objectManager;
		}
		//'\Magento\Store\Model\StoreManagerInterface'
		return $this->_objectManager->get($class);
	}
}