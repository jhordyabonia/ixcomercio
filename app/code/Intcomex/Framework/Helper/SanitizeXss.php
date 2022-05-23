<?php

namespace Intcomex\Framework\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Zend_Log_Exception;

class SanitizeXss extends AbstractHelper
{
    /**
     * Keys to no validate.
     */
    const IGNORED_KEYS = ['form_key', 'current_password', 'password', 'password_confirmation'];

    /**
     * @param Context $context
     * @throws Zend_Log_Exception
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/xss.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $this->_logger = $logger;
    }

    /**
     * Sanitize each field.
     *
     * @param $data
     * @return array
     */
    public function sanitize($data): array
    {
        $this->_logger->debug(json_encode($data));
        $arraySanitized = [];
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    if (!in_array($key, self::IGNORED_KEYS)) {
                        $arraySanitized[$key] = $this->_sanitize($value);
                    } else {
                        $arraySanitized[$key] = $value;
                    }
                }
                // Validate to street
                if (is_array($value)) {
                    foreach ($value as $item) {
                        $arraySanitized[$key][] = $this->_sanitize($item);
                    }
                }
            }
        }
        $this->_logger->debug(json_encode($arraySanitized));
        return $arraySanitized;
    }

    /**
     * Sanitize string.
     *
     * @param $string
     * @return string
     */
    private function _sanitize($string): string
    {
        return $string ? trim(htmlspecialchars(strip_tags(trim($string)))) : '';
    }
}
