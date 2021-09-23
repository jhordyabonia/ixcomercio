<?php

namespace Intcomex\FormatPrice\Model\Plugin;

class Currency extends PriceFormatPluginAbstract
{

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\CurrencyInterface $subject
     * @param array                                ...$args
     *
     * @return array
     */
    public function beforeToCurrency(
        \Intcomex\FormatPrice\Model\Currency $subject,
        ...$arguments
    ) {
        if ($this->getConfig()->isEnable()) {
            $arguments[1]['precision'] = $subject->getPricePrecision();
        }
        return $arguments;
    }
}
