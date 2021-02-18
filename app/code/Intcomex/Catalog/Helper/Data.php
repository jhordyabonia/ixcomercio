<?php
namespace Intcomex\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper{

	const MINPRICE = 'catalog/price/minimal_price';
	const MAXDISC = 'catalog/price/maximum_discount';

	protected $_coreRegistry;

	/**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
	
	public function __construct(
		\Magento\Catalog\Block\Product\Context $productContext,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	){
		$this->_coreRegistry = $productContext->getRegistry();
		$this->scopeConfig = $scopeConfig;	
	}
	
	/**
     * Get current product
     * @return mixed
     */
	public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
	}
	
	//Obtiene los parámetros de configuración desde el cms
    public function getConfigParams() 
    {
		//Se obtienen parametros de configuración por Store
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $configData['minimal_price'] = $this->scopeConfig->getValue(self::MINPRICE, $storeScope);
        $configData['maximum_discount'] = $this->scopeConfig->getValue(self::MAXDISC, $storeScope);
		return $configData;
	}
}