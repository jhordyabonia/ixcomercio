<?php

namespace Intcomex\FormatPrice\Model\Plugin;

use Intcomex\FormatPrice\Helper\Data;
use Intcomex\FormatPrice\Model\Config;


class Amount extends \Magento\Framework\Pricing\Render\Amount
{

    protected $_helperData;
    protected $_configModule;

    /**
  * @param PriceCurrencyInterface $priceCurrency
  */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        Data $helperData,
        Config $configModule
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_helperData = $helperData;
        $this->_configModule = $configModule;
    }

    /**
     * Format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @return float
     */
    public function afterFormatCurrency(\Magento\Framework\Pricing\Render\Amount $subject, $result)
    {

        $amount = $subject->getAmount()->getValue();
        $includeContainer = true;
        $precision = null;

        if( $this->_configModule->isEnable() ){
        
            if (floor($amount) == $amount) {

                var_dump($amount);

                $currency = $this->_helperData->getCurrentCurrencySymbol();

                //return $this->priceCurrency->format($amount, $includeContainer, $precision = 0);

                //return $currency.$amount;

                //$currency.$block->getDisplayValue()
            }
        }
        
        return $result;
    }
    
}
