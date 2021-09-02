<?php

namespace Intcomex\FormatPrice\Model\Plugin;

use Intcomex\FormatPrice\Model\ConfigInterface;
use Intcomex\FormatPrice\Model\PricePrecisionConfigTrait;

abstract class PriceFormatPluginAbstract
{

    use PricePrecisionConfigTrait;

    /** @var ConfigInterface  */
    protected $moduleConfig;

    /**
     * @param \Intcomex\FormatPrice\Model\ConfigInterface $moduleConfig
     */
    public function __construct(
        ConfigInterface $moduleConfig
    ) {
        $this->moduleConfig  = $moduleConfig;
    }
}
