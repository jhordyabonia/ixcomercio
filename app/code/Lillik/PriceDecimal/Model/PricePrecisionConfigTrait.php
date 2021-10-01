<?php

namespace Lillik\PriceDecimal\Model;

trait PricePrecisionConfigTrait
{


    /**
     * @return \Lillik\PriceDecimal\Model\ConfigInterface
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
