<?php

namespace Intcomex\Framework\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class SanatizeXss extends AbstractHelper
{
    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @param $data
     * @return array
     */
    public function sanatize($data): array
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/xss.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $this->_logger = $logger;
        $this->_logger->debug(json_encode($data));
        $arraySanatized = [];
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $arraySanatized[$key] = htmlspecialchars(strip_tags(trim($value)));
            }
        }
        $this->_logger->debug(json_encode($arraySanatized));
        return $arraySanatized;
    }
}
