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

	/* Valida si un código http es válido */
	private function isValidCode($code){
		$valid = array(0, 1, 2 , 3);
		$code = substr($code, 0, 1);
		return in_array($code, $valid);
	}

	/*Consulta proceso cUrl */
	public function makeCurl($wsdl, $header, $logger){
		//Inicia Curl
        $logger->info('Inicia consulta del WS');
		$logger->info('endpoint - '.$wsdl);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $wsdl,
        ));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		
		//Ejecuta Curl
		$resp = curl_exec($curl);
        $curl_errors = curl_error($curl);
		$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		//Valida errores
		if($curl_errors){
			throw new \Exception(sprintf('Curl error: %s', $curl_errors));
		}
		
		//Verifica si el código es válido
        $logger->info('status code: '.$status_code);
		if(!$this->isValidCode($status_code)){
			throw new \Exception(sprintf('http status code error %s', $status_code));
		}

		//Decodifica el json
		$resp = json_decode($resp);

		//Verifica la respuesta es un json válido
		if(json_last_error() != JSON_ERROR_NONE){
			$er = json_last_error_msg();
			$code = json_last_error();
			throw new \Exception(sprintf('Server responds an invalid json. %s (%s)', $er, $code));
		}
	
		return array(
			'status' => true,
			'resp' => $resp
		);
	}
}