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

            $response = ['success' => true, 'cities' => $regions ];

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
        $response = ['success' => false, 'message' => "Hello cities ".$countryCode ];

        //Select Data from table
        $sql = "Select * FROM trax_places_cities where parent_id='".$parent_id."'";
       
        $place = $this->connection->fetchAll($sql); 
        foreach ($place as $key => $data) {
            return $data['id'];
        }

        return json_encode($response);
    }

}