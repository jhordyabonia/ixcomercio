<?php
namespace FlixMedia\Js\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper{

	const ID = 'flix_general/catalogo_retailer/apikey';
	const LANGUAGE = 'flix_general/catalogo_retailer/language';

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
        $configData['id'] = $this->scopeConfig->getValue(self::ID);
		$configData['language'] = $this->scopeConfig->getValue(self::LANGUAGE);
		return $configData;
	}
}