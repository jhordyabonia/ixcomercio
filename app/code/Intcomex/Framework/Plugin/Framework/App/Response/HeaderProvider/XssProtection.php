<?php
namespace Intcomex\Framework\Plugin\Framework\App\Response\HeaderProvider;

class XssProtection
{
    public function aroundGetValue(
        \Magento\Framework\App\Response\HeaderProvider\XssProtection $subject,
        \Closure $proceed
    ) {
        return 0;
    }
}