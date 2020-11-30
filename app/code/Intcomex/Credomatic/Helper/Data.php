<?php
namespace  Intcomex\Credomatic\Helper;
use \Psr\Log\LoggerInterface;


class Data extends \Magento\Payment\Helper\Data{

    /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
    protected $scopeConfig;

    protected $_logger;

    /**
     * Factory for payment method models
     *
     * @var \Magento\Payment\Model\Method\Factory
     */
    protected $_methodFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_methodFactory = $paymentMethodFactory;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/paymentModels.log');
        $this->_logger = new \Zend\Log\Logger();
        $this->_logger->addWriter($writer);
    }

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
