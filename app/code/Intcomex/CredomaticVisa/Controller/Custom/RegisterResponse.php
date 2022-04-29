<?php
namespace Intcomex\CredomaticVisa\Controller\Custom;

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
     * Execute view action.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Validator\Exception
     */
    public function execute()
    {
        try {
            $get = $this->getRequest()->getParams();
            if(!empty($get)){
                $model =  $this->_credomaticFactory->create();  
                $data = $model->load($get['token'],'token');
                
                if(!empty($data->getData())){ 
                    $model->setResponse($this->json->serialize($get));
                    $model->setUpdatedAt();
                    $model->save();
                }

            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage())); 
        }
    }
}
