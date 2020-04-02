<?php
namespace FlixMedia\Js\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper{

	protected $_coreRegistry;
	
	public function __construct(
		\Magento\Catalog\Block\Product\Context $productContext
	){
		$this->_coreRegistry = $productContext->getRegistry();	
	}
	
	/**
     * Get current product
     * @return mixed
     */
	public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }
}