<?php

namespace Intcomex\Credomatic\Controller\Custom;


class GetResponse extends \Magento\Framework\App\Action\Action
{


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Intcomex\Credomatic\Model\Credomatic $credomaticFactory,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        parent::__construct($context);
        $this->credomaticFactory = $credomaticFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->json = $json;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){ 
        $dataToPost = array();
        try {
            $resultJson = $this->resultJsonFactory->create();
           //$post  = $this->getRequest()->getPostValue();
           $post = $this->getRequest()->getParams();
            if(!empty($post)){

                $data = $this->credomaticFactory->getCollection()->addFieldToFilter('order_id', array('eq' => $post['order_id']));
                if(!empty($data->getData())){
                    $json = $data->getData()[0]['response'];
                   $resp = http_build_query($this->json->unserialize($json));
                    $dataToPost['info'] = $resp;
                    $dataToPost['status'] = 'success';
                }else{
                    $dataToPost['status'] = 'error';
                }
            }
    
        } catch (\Exception $e) {
            $dataToPost = ['status' => 'error', 'message' => $e->getMessage()];
        }
        $resultJson->setData($dataToPost);
        return $resultJson;
        
    }


}