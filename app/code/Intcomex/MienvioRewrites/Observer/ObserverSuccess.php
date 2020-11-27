<?php
namespace Intcomex\MienvioRewrites\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory;
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface;
use MienvioMagento\MienvioGeneral\Helper\Data as Helper;

class ObserverSuccess extends MienvioMagento\MienvioGeneral\Observer\ObserverSuccess
{

    /**
     * Retrieves total measures of given items
     *
     * @param  Items $items
     * @return
     */
    private function getOrderDefaultMeasures($items)
    {
        $this->_logger->debug('Items', ['data' => $items]);
        $packageVolWeight = 0;
        $orderLength = 0;
        $orderWidth = 0;
        $orderHeight = 0;
        $orderDescription = '';
        $itemsArr = [];

        foreach ($items as $item) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productName = $item->getName();
            $orderDescription .= $productName . ' ';
            $product = $objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('name', $productName);
            if($product->getData('iws_type') == 'Kit'){

                $serviceUrl = $this->getServiceUrl($item->getSku());
				if(isset($serviceUrl) && !empty($serviceUrl)){ 
					$itemsKit = $this->beginProductLoad($serviceUrl, 0);
					if(isset($itemsKit) && !empty($itemsKit)){
						foreach($itemsKit as $itemKit){
							if($this->_mienvioHelper->getMeasures() === 1){
								$length = $itemKit->Freight->Item->Length;
								$width  = $itemKit->Freight->Item->Width;
								$height = $itemKit->Freight->Item->Height;
								$weight = $itemKit->Freight->Item->Weight;
				
							}else{
								$length = $this->convertInchesToCms($itemKit->Freight->Item->Length);
								$width  = $this->convertInchesToCms($itemKit->Freight->Item->Width);
								$height = $this->convertInchesToCms($itemKit->Freight->Item->Height);
								$weight = $this->convertWeight($itemKit->Freight->Item->Weight);
							}
				
							$orderLength += $length;
							$orderWidth  += $width;
							$orderHeight += $height;
				
							$volWeight = $this->calculateVolumetricWeight($length, $width, $height);
							$packageVolWeight += $volWeight;
				
							$itemsArr[] = [
								'id' => $itemKit->Sku,
								'name' => $itemKit->Description,
								'length' => $length,
								'width' => $width,
								'height' => $height,
								'weight' => $weight,
								'volWeight' => $volWeight,
								'qty' => $itemKit->Quantity,
								'declared_value' => $itemKit->Price,
							];
						}
					}
				} else {
					$this->_logger->info('GetProduct - No se genero url del servicio');
				}
            }else{

                if($this->_mienvioHelper->getMeasures() === 1){
                    $length = $product->getData('ts_dimensions_length');
                    $width  = $product->getData('ts_dimensions_width');
                    $height = $product->getData('ts_dimensions_height');
                    $weight = $product->getData('weight');
    
                }else{
                    $length = $this->convertInchesToCms($product->getData('ts_dimensions_length'));
                    $width  = $this->convertInchesToCms($product->getData('ts_dimensions_width'));
                    $height = $this->convertInchesToCms($product->getData('ts_dimensions_height'));
                    $weight = $this->convertWeight($product->getData('weight'));
                }
    
                $orderLength += $length;
                $orderWidth  += $width;
                $orderHeight += $height;
    
                $volWeight = $this->calculateVolumetricWeight($length, $width, $height);
                $packageVolWeight += $volWeight;
    
                $itemsArr[] = [
                    'id' => $item->getSku(),
                    'name' => $productName,
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'weight' => $weight,
                    'volWeight' => $volWeight,
                    'qty' => $item->getQtyordered(),
                    'declared_value' => $item->getprice(),
                ];
            }


        }

        return [
            'vol_weight'  => $packageVolWeight,
            'length'      => $orderLength,
            'width'       => $orderWidth,
            'height'      => $orderHeight,
            'description' => $orderDescription,
            'items'       => $itemsArr
        ];
    }




    private  function parseReverseServiceLevel($serviceLevel){
        $parsed = '';
        switch ($serviceLevel) {
            case 'Estándar' :
                $parsed = 'estandar';
                break;
            case 'Express' :
                $parsed = 'express';
                break;
            case 'Saver' :
                $parsed = 'saver';
                break;
            case 'Express Plus' :
                $parsed = 'express_plus';
                break;
            case 'Economy' :
                $parsed = 'economy';
                break;
            case 'Priority' :
                $parsed = 'priority';
                break;
            case 'World Wide USA' :
                $parsed = 'worlwide_usa';
                break;
            case 'World Wide USA' :
                $parsed = 'worldwide_usa';
                break;
            case 'Regular' :
                $parsed = 'regular';
                break;
            case 'Regular MX' :
                $parsed = 'regular_mx';
                break;
            case 'Priority' :
                $parsed = 'BE_priority';
                break;
            case 'Flex' :
                $parsed = 'flex';
                break;
            case 'Programado' :
                $parsed = 'scheduled';
                break;
            default:
                $parsed = $serviceLevel;
        }

        return $parsed;

    }

    public function getServiceUrl($sku)
	{
        $apiKeyTrax = $this->helperDataCdi->getStoreConfig(self::API_KEY);
        $accessKeyTrax = $this->helperDataCdi->getStoreConfig(self::ACCESS_KEY);
        $locale = 'es';
		if($apiKeyTrax == ''){
            $serviceUrl = false;
        } else {
            $utcTime = gmdate("Y-m-d").'T'.gmdate("H:i:s").'Z';
            $signature = $apiKeyTrax.','.$accessKeyTrax.','.$utcTime;
            $signature = hash('sha256', $signature);
            $serviceUrl = $this->_mienvioHelper->getKitUrlService().'?locale='.$locale.'&sku='.$sku.'&apiKey='.$apiKeyTrax.'&utcTimeStamp='.$utcTime.'&signature='.$signature;
        }
        return $serviceUrl;
    }

    //Función recursiva para intentos de conexión
    public function beginProductLoad($serviceUrl, $attempts) 
    {
        //Se conecta al servicio 
        $data = $this->loadIwsService($serviceUrl);
        $this->_logger->info('Response:');
        $this->_logger->info($data);
        if($data['status']){
            return $data['resp']->Components;
        } else {
			if($this->_mienvioHelper->getKitRetries()>$attempts){
				$attempts++;
				$this->_logger->info('GetProduct - Error conexión: '.$serviceUrl);
				sleep(30);
				$this->_logger->info('GetProduct - Se reintenta conexión #'.$attempts.' con el servicio.');
				$this->beginProductLoad($serviceUrl, $attempts);
			} else{
				$this->_logger->info('GetProduct - Error conexión: '.$serviceUrl);
				$this->_logger->info('GetProduct - Se cumplieron el número de reintentos permitidos ('.$attempts.') con el servicio: '.$serviceUrl.' se envia notificación al correo '.$this->_mienvioHelper->getKitEmail());
				$this->email->notify('Soporte Trax', $this->_mienvioHelper->getKitEmail(), $this->_mienvioHelper->getKitRetries(), $serviceUrl, 'N/A', '');
			}
        }   

    }

    //Carga el servicio de IWS por Curl
    public function loadIwsService($serviceUrl) 
    {
        $this->_curl->get($serviceUrl);
        $this->_logger->info('GetProduct - '.$serviceUrl);
		$response = array(
			'status' => true,
			'resp' => json_decode($this->_curl->getBody())
		);
        return $response;
    }
}
