<?php
namespace  Incomex\Credomatic\Helper;

class Data extends Magento\Payment\Helper{

    /**
     * Retrieve method model object
     *
     * @param string $code
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return MethodInterface
     */ 
    public function getMethodInstance($code)
    {
        $paymentMethod = $this->getMethodModelConfigName($code);

        $class = $this->scopeConfig->getValue(
            $this->getMethodModelConfigName($code),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if(!$class){
            $this->_logger->info('El modelo indicado en '.$paymentMethod.' no esta definido!');
            
            if(strcmp($paymentMethod,'payment/Credomatic/model')===0){
                $class = 'Intcomex\Credomatic\Model\Payment';
            }else if(strcmp($paymentMethod,'payment/Pagalo/model')===0){
                $class = 'Magento\Pagalo\Model\Payment';
            }
            $this->_logger->info('Para el modelo '.$paymentMethod.' se inicializo con '.$class);
        }
        
        if (!$class) {
            throw new \UnexpectedValueException('Payment model name is not provided in config!');
        }

        return $this->_methodFactory->create($class);
    }
    
}