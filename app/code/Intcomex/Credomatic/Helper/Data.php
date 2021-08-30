<?php
namespace  Intcomex\Credomatic\Helper;

use \Magento\Payment\Helper\Data as mainHelper;

use Magento\Store\Model\ScopeInterface;

class Data extends mainHelper{ 

    /**
     * Retrieve method model object
     *
     * @param string $code
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return MethodInterface
     */ 
    public function getMethodInstance($code){
        $paymentMethod = $this->getMethodModelConfigName($code);

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/paymentModelsFix.log');
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);

        $class = $this->scopeConfig->getValue(
            $this->getMethodModelConfigName($code),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

        if(!$class){

            $this->logger->info('El modelo indicado en '.$paymentMethod.' no esta definido!');
        
            $class =  $_scopeConfig->getValue('payment/'.$code.'/model',ScopeInterface::SCOPE_STORE);
        
            $this->logger->info('Para el modelo '.$paymentMethod.' se inicializo con '.$class);
        } 
        
        if (!$class) {
            throw new \UnexpectedValueException('Payment model name is not provided in config!');
        }

        return $this->_methodFactory->create($class);
    }
    
}
