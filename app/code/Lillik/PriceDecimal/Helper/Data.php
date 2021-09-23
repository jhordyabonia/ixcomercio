<?php
namespace Intcomex\FormatPrice\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Intcomex\FormatPrice\Model\Config;

class Data extends AbstractHelper{

	protected $_priceCurrency;
	protected $_configModule;

	public function __construct(
		\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
		Config $configModule
	)
	{           
		$this->_priceCurrency = $priceCurrency;
		$this->_configModule = $configModule;
	}

	public function getCurrentCurrencySymbol()
	{
		return $this->_priceCurrency->getCurrency()->getCurrencySymbol();
	}


	public function isModuleEnabled()
	{
		return $this->_configModule->isEnable();
	}
}