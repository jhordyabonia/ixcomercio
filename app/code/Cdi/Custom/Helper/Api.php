<?php
namespace Cdi\Custom\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;

class Api extends AbstractHelper{

	
	const NOT_INVOICED_CLOSED = 'shipping/mail_notification/mail_notification_invoce';
	const NOT_INVOICED_CANCELED = 'shipping/mail_notification/mail_notification_cancel';
	const NOT_INVOICED_INVOICE_CREATED = 'shipping/mail_notification/mail_notification_created';
	const NOT_INVOICED_LABEL_CREATED = 'shipping/mail_notification/mail_notification_label';
	const NOT_INVOICED_TRANSITO = 'shipping/mail_notification/mail_notification_transito';
	const NOT_INVOICED_ENTREGADO = 'shipping/mail_notification/mail_notification_entregado';
	const NOT_INVOICED_DEFAULT = 'shipping/mail_notification/mail_notification_default';
	

 
	protected $_scopeConfig;
	protected $_storeManager;
	protected $_objectManager;
	protected $_orderCollectionFactory;
	protected $_iwsOrder;

	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Trax\Ordenes\Model\IwsOrderFactory $iwsOrder
	){
		$this->_scopeConfig = $scopeConfig;
		$this->_storeManager = $storeManager;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		$this->_iwsOrder = $iwsOrder;
	}

	public function getCurrentStore($codeOnly = false){
		$store = $this->_storeManager->getStore();
		if($codeOnly) return $store->getCode();
		return $store;
	}

	/* Genera la url para consultas de TRAX */
	public function prepateTraxUrl($method, $configData, $params, $logger){
		$logger->info('Inicia contrucción de url TRAX');
		$storeCode = $this->getCurrentStore(true);
		//Valida que el api key no esté vacío
		if($configData['apikey'] == '')
            throw new \Exception("Empty api key");
        
		$utcTime = gmdate("Y-m-d").'T'.gmdate("H:i:s").'Z';
		$signature = $configData['apikey'].','.$configData['accesskey'].','.$utcTime;
		$signature = hash('sha256', $signature);
		//Contruye la url
		$serviceUrl = "{$configData['url']}{$method}";
		//Obtiene los parámetros de la url
		$params['locale'] = 'en';
		$params['apiKey'] = $configData['apikey'];
		$params['utcTimeStamp'] = $utcTime;
		$params['signature'] = $signature;
		$params['tag'] = '';
		$params['generateTokens'] = 'false';
		$paramsStr = http_build_query($params);
		//Retorna la url final
		$serviceUrl.= "?{$paramsStr}";
		$logger->info("Retorna la url de servicio {$serviceUrl}");
        return $serviceUrl;
	}

	//Obtiene los parámetros de configuración desde el cms
	public function getConfigParams($fields, $store = false)
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$storeCode = ($store) ? $store : $this->getCurrentStore(true);
		$configData = array();
		foreach($fields as $key => $path){
			$configData[$key] = $this->_scopeConfig->getValue($path, $storeScope, $storeCode);
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

	/* Retorna una orden según los filtros indicados*/
	public function getMagentoOrderBy($fieldFilter, $logger){
		$logger->info("Realiza búsqueda de objeto sales_order");
		$collection = $this->_orderCollectionFactory->create()->addFieldToSelect('*');
		foreach($fieldFilter as $filter){
			$field = $filter[0];
			$val = $filter[1];
			$logger->info("Filtro: {$field} => {$val}");
			$collection->addFieldToFilter($field, $val);
		}
		
        if(!$collection->getSize())
			throw new \Exception('No fue posible obtener una orden con los filtros indicados');
			
        $order = $collection->getFirstItem();
        $logger->info("Magento order_id: {$order->getEntityId()}");
        return $order;
	}

	/* Retorna información de la tabla IWS_order según los filtros indicados*/
	public function getIwsOrderBy($field, $val, $logger, $throw = true){
		if($logger) $logger->info("Realiza búsqueda de registro en iws_order");
		if($logger) $logger->info("Filtro: {$field} => {$val}");
		$iwsOrder = $this->_iwsOrder->create();
		$iwsOrder->getResource()->load($iwsOrder, $val, $field);
		if(!$iwsOrder || !$iwsOrder->getId()){
			if($throw){
				throw new \Exception('No fue posible obtener una orden con los filtros indicados');
			}else{
				return false;
			}

		}
		if($logger) $logger->info("iws_order table id: {$iwsOrder->getId()}");
		return $iwsOrder;
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

	/* Permite realizar dump */
	public function dump($obj, $die = true, $title = null){
		echo "<pre>";
		if(!is_null($title)) echo "<h2>{$title}</h2>";
		if(is_array($obj)){
			print_r($obj);
		}elseif(is_string($obj)){
			echo $obj;
		}else{
			var_dump($obj);
		}
		echo "<pre>";
		if($die) die();
	}

	/*Consulta proceso cUrl */
	public function makeCurl($wsdl, $header = false, $logger, $retry = 0, $sleep = 3){
		//Inicia Curl
		$logger->info('Inicia consulta del WS');
		
		//Verifica el cUrl
		if(!extension_loaded("cURL"))
			throw new \Exception('An error occurred and processing, please verify local CURL extension');
		
		$logger->info('endpoint - '.$wsdl);
		
		//Cantidad máxima de intentos: un intento + los reintentos parametrizados
		$maxTry = 1 + $retry;
		$try = 1;
		$resp = null;
		while(is_null($resp)){
			try{
				$logger->info("Intento {$try} de {$maxTry}");
				$resp = $this->curl($wsdl, $header, $logger);
			}catch(\Exception $e){
				$logger->info("Ocurrió un error: {$e->getMessage()}");
				if($try < $maxTry){
					$try++;
					sleep($sleep);
				}else{
					throw $e;
				}				
			}
		}	
		return array(
			'status' => true,
			'resp' => $resp
		);
	}
	
	private function curl($wsdl, $header, $logger){
		$curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $wsdl,
		));
		//Agrega la cabecera si es necesario
		if(is_array($header))
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
		$logger->info('Response:');
		$logger->info(print_r($resp, true));
		return $resp;
	}

	/*Retorna estado y comentario para un pedido */
	public function getCommentByStatus($st, $type = 'shipment'){
		$trakStr = '';
		
		// active notification mail customer
		$notify_mail = $this->getActiveMail();

		switch($type){
			//Mensaje para envíos
			case 'shipment':
				if(isset($st['label_url'])){
					$trakStr = sprintf(
						__(
							"Tu número de guía es N° %s\n\nVerifica el estado de tu pedido aquí:\n%s\n\nVerifica tu guía aquí:\n%s"
						),
						$st['tracking_number'],
						$st['tracking_url'],
						$st['label_url']
					);
				}
				break;
			//Mensaje para facturas
			case 'invoice':
				if(isset($st['InvoiceUrl'])){
					$trakStr = sprintf(
						__(
							//@TODO: texto para visualización de factura
							"Tu número de factura es N° %s\n\nVerifica tu factura aquí:\n%s"
						),
						$st['InvoiceNumber'],
						$st['InvoiceUrl']
					);
				}
				break; 
		}
		
		if(!isset($st['statusCode'])) $st['statusCode'] = '';
		
        $status = array(
			'Invoiced/Closed' => array(
				//@TODO: texto de estado de la orden: cerrada cron
                'msg' => sprintf(
					__("Se cierra la orden vía cron_trax. Estado %s (%s)"),
					$st['status'],
					$st['statusCode']
				),
                'notify' => $notify_mail['invoiced_closed'],
				'newstatus' => 'closed',
				'frontlabel' => __('Orden cerrada vía cron_trax.')
			),
			'Canceled' => array(
				//@TODO: texto de estado de la orden: cerrada cron
                'msg' => sprintf(
					__("Se cancela la orden vía cron_trax. Estado %s (%s)"),
					$st['status'],
					$st['statusCode']
				),
                'notify' => $notify_mail['Canceled'],
				'newstatus' => 'canceled',
				'frontlabel' => __('Orden cancelada vía cron_trax.')
			),
			'INVOICE_CREATED' => array(
                //@TODO: texto de estado de la orden: tránsito
                'msg' => sprintf(
					__("¡Hemos generado la factura de tu pedido!\n\n%s"),
					$trakStr
				),
                'notify' => $notify_mail['INVOICE_CREATED'],
				'newstatus' => false,
				'frontlabel' => __('¡Hemos generado la factura de tu pedido!')
            ),
			'LABEL_CREATED' => array(
                //@TODO: texto de estado de la orden: tránsito
                'msg' => sprintf(
					__("¡Hemos generado la guía de tu pedido!\n\n%s"),
					$trakStr
				),
                'notify' => $notify_mail['LABEL_CREATED'],
				'newstatus' => false,
				'frontlabel' => __('¡Hemos generado la guía de tu pedido!')
            ),
			'TRANSITO' => array(
                //@TODO: texto de estado de la orden: tránsito
                'msg' => sprintf(
					__("¡Tu paquete está por llegar!\n\n%s"),
					$trakStr
				),
                'notify' => $notify_mail['TRANSITO'],
				'newstatus' => false,
				'frontlabel' => __('¡Tu paquete está por llegar!')
            ),
            'ENTREGADO' => array(
                //@TODO: texto de estado de la orden: entregado
                'msg' => sprintf(
					__("¡Tu paquete ha sido entregado!\n\n%s"),
					$trakStr
				),
                'notify' => $notify_mail['ENTREGADO'],
				'newstatus' => 'complete',
				'frontlabel' => __('¡Tu paquete ha sido entregado!')
            ),
        );
        if(isset($st['status'], $status[$st['status']])) return $status[$st['status']];
		if($type == 'invoice_curl'){
			return false;
		}else{
			return array(
				//@TODO: texto de estado de la orden: desconocido
				'msg' => __('Estado desconocido'),
				'notify' => $notify_mail['default'],
				'newstatus' => false,
				'frontlabel' => __('Error al obtener el estado del pedido, contacte al administrador.')
			);
		}
	}

	/*Retorna active/false mail notification */
	public function getActiveMail(){

		$status = array();

		$status['invoiced_closed'] = $this->_scopeConfig->getValue(
			//'general/locale/weight_unit',
			self::NOT_INVOICED_CLOSED,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		
		$status['Canceled'] = $this->_scopeConfig->getValue(
            self::NOT_INVOICED_CANCELED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		$status['INVOICE_CREATED'] = $this->_scopeConfig->getValue(
            self::NOT_INVOICED_INVOICE_CREATED			,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		$status['LABEL_CREATED'] = $this->_scopeConfig->getValue(
            self::NOT_INVOICED_LABEL_CREATED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		$status['TRANSITO'] = $this->_scopeConfig->getValue(
            self::NOT_INVOICED_TRANSITO,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		$status['ENTREGADO'] = $this->_scopeConfig->getValue(
            self::NOT_INVOICED_ENTREGADO,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		$status['default'] = $this->_scopeConfig->getValue(
            self::NOT_INVOICED_DEFAULT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		return $status;
	}
}