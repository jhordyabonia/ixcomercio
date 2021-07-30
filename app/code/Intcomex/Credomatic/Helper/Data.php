<?php
namespace  Intcomex\Credomatic\Helper;

use \Magento\Payment\Helper\Data as mainHelper;

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

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/paymentModels.log');
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);

        $class = $this->scopeConfig->getValue(
            $this->getMethodModelConfigName($code),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if(!$class){
            $this->logger->info('El modelo indicado en '.$paymentMethod.' no esta definido!');
            
            if(strcmp($paymentMethod,'payment/credomatic/model')===0){
                $class = 'Intcomex\Credomatic\Model\Payment';
            }else if(strcmp($paymentMethod,'payment/pagalo/model')===0){
                $class = 'Magento\Pagalo\Model\Payment';
            }else if(strcmp($paymentMethod,'payment/pagalovisa/model')===0){
                $class = 'Magento\PagaloVisa\Model\Payment';
            }else if(strcmp($paymentMethod,'payment/pagalomastercard/model')===0){
                $class = 'Magento\PagaloMasterCard\Model\Payment';
            }else if(strcmp($paymentMethod,'payment/credomaticvisa/model')===0){
                $class = 'Magento\CredomaticVisa\Model\Payment';
            }else if(strcmp($paymentMethod,'payment/credomaticmastercard/model')===0){
                $class = 'Magento\CredomaticMasterCard\Model\Payment';
            }
            $this->logger->info('Para el modelo '.$paymentMethod.' se inicializo con '.$class);
        } 
        
        if (!$class) {
            throw new \UnexpectedValueException('Payment model name is not provided in config!');
        }

        return $this->_methodFactory->create($class);
    }
    
}
