<?php

namespace Intcomex\FormatPrice\Model;

trait PricePrecisionConfigTrait
{


    /**
     * @return \Intcomex\FormatPrice\Model\ConfigInterface
     */
    public function getConfig()
    {
        return $this->moduleConfig;
    }

    /**
     * @return int|mixed
     */
    public function getPricePrecision()
    {
        if ($this->getConfig()->canShowPriceDecimal()) {        

            return $this->getConfig()->getPricePrecision();

        }

        return 0;
    }
}
