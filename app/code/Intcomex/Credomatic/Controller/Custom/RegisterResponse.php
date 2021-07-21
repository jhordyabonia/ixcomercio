<?php

namespace Intcomex\Credomatic\Controller\Custom;

use Magento\Framework\App\ResourceConnection;

class RegisterResponse extends \Magento\Framework\App\Action\Action
{


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ResourceConnection $resource,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        parent::__construct($context);
        $this->resource   = $resource;
        $this->connection  = $resource->getConnection();
        $this->json = $json;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {
            $post = $this->getRequest()->getParams();
            if(!empty($post)){

                $data = [
                    ['id' => null, 
                    'order_id' => $post['orderid'],
                    'response' => $this->json->serialize($post),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    ],
                ];
    
                $tableName = $this->resource->getTableName('transacciones_credomatic');
                $insertData = $this->connection->insertMultiple($tableName, $data);

            }
    
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }
        
    }


}