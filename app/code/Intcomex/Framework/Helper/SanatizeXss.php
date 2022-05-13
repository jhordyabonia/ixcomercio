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
        $arraySanatized = [];
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $arraySanatized[$key] = htmlspecialchars(strip_tags(trim($value)));
            }
        }
        return $arraySanatized;
    }
}
