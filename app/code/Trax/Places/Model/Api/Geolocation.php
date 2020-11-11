<?php

namespace Trax\Places\Model\Api;

use Psr\Log\LoggerInterface;

class Geolocation
{
    protected $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;


    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $connection;

    public function __construct(
        LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->logger = $logger;
        $this->request = $request;

        // connection DB
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->connection = $resource->getConnection();

    }

    /**
     *  @inheritdoc
     */

    public function getRegions()
    {       

        $params = $this->request->getParams();

        $countryCode = trim($params['countryCode'] ?? null);    

        //Select Data from table
        $sql = "Select * FROM trax_places_regions where country_id='".$countryCode."'";
    
        $place = $this->connection->fetchAll($sql); 

        if(count($place)> 0){
            $regions = array();
            foreach ($place as $key => $data) {

                $regions[] = [
                    'id' =>  $data['id'],
                    'name' => $data['name'],
                    'traxId' => $data['trax_id']
                ];
            }

            $response = ['success' => true, 'regions' => $regions ];

        }else{
            $response = ['success' => false, 'message' => "No found regions for ".$countryCode ];
        }   

        return json_encode($response);
    }

    /**
     *  @inheritdoc
     */

    public function getCities()
    {       

        $params = $this->request->getParams();

        $parent_id = trim($params['parentId'] ?? null);
        //$response = ['success' => false, 'message' => "Hello cities ".$countryCode ];

        //Select Data from table
        
        $sql = "Select * FROM trax_places_cities where parent_id='".$parent_id."'";
        $cities = $this->connection->fetchAll($sql); 


        if(count($cities)> 0){      
            
            $cities_array = array();
            foreach ($cities as $key => $data) {

                $cities_array[] = [
                    'id' =>  $data['id'],
                    'name' => $data['name'],
                    'traxId' => $data['trax_id'],
                    'countryId' => $data['country_id']
                ];
            }

            $response = ['success' => true, 'cities' => $cities_array ];

        }else{
            $response = ['success' => false, 'message' => "No found cities for parent_id: ".$parent_id];
        } 

        return json_encode($response);
    }


    /**
     *  @inheritdoc
     */

    public function getZones()
    {       

        $params = $this->request->getParams();

        $parent_id = trim($params['parentId'] ?? null);
        
        $sql = "Select * FROM trax_places_localities where parent_id='".$parent_id."'";
        $localitaties = $this->connection->fetchAll($sql); 

        if(count($localitaties)> 0){      
            
            $localitaties_array = array();
            foreach ($localitaties as $key => $data) {
                $localitaties_array[] = [
                    'id' =>  $data['id'],
                    'name' => $data['name'],
                    'traxId' => $data['trax_id'],
                    'countryId' => $data['country_id'],
                    'postalCode' => $data['postal_code'],
                    'parentId' => $data['parent_id']
                ];
            }

            $response = ['success' => true, 'localitaties' => $localitaties_array ];

        }else{
            $response = ['success' => false, 'message' => "No found localities for parent_id: ".$parent_id];
        } 

        return json_encode($response);
    }

}