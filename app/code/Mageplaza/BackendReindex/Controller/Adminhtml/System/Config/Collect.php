<?php

namespace Mageplaza\BackendReindex\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Collect extends Action
{

    protected $resultJsonFactory;


    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\Crontab\CrontabManagerInterface $crontabManager,
        \Magento\Framework\Crontab\TasksProviderInterface $tasksProvider 
        
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->crontabManager = $crontabManager;
        $this->tasksProvider = $tasksProvider;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute(){ 
        $resultJson = $this->resultJsonFactory->create();
        $post  = $this->getRequest()->getPostValue();
        $respProcess = array();
        try {

            //if ($this->crontabManager->getTasks()) {
            //}
          $resul =  $this->crontabManager->removeTasks();
          echo '<pre>';
          print_r($resul);
          echo '</pre>';
            //$tasks = $this->tasksProvider->getTasks();
            $tasks['cronMagento']['command'] = '50 * * * * /usr/bin/php7.2 /var/www/whitelabel/bin/magento';
           //  $this->crontabManager->saveTasks($tasks);
            
            $respProcess = ['success' => true, 'message' => 'Test Data'];
       
        } catch (\Exception $e) {
            $respProcess = ['status' => 'error', 'message' => $e->getMessage()];
        }
        $resultJson->setData($respProcess);
        return $resultJson;
        
    }

}