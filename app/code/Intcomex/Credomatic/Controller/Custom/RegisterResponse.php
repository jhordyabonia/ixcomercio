<?php

namespace Intcomex\Credomatic\Controller\Custom;

use Intcomex\Credomatic\Model\CredomaticFactory;

class RegisterResponse extends \Magento\Framework\App\Action\Action
{


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Intcomex\Credomatic\Model\CredomaticFactory $credomaticFactory
    ) {
        parent::__construct($context);
        $this->json = $json;
        $this->_credomaticFactory = $credomaticFactory;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        try {
            $get = $this->getRequest()->getParams();

            if(!empty($get)){
                $model =  $this->_credomaticFactory->create();  
                $data = $model->getCollection()->addFieldToFilter('order_id', array('eq' => $get['orderid']));
                
                if(empty($data->getData())){
                    $model->addData([
                        'order_id' => $get['orderid'],
                        'response' => $this->json->serialize($get),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                        ]);
    
                     $model->save();
                }

            }
    
        } catch (\Exception $e) {
            $error = __('Payment create data error Credomatic: '); 
            throw new \Magento\Framework\Validator\Exception(__($error.$e->getMessage())); 
        }
        
    }


}