<?php
namespace FlixMedia\Js\Helper;

use Magento\Framework\Registry;

class Data extends AbstractHelper{

	/**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
	
	/**
	 * @param \Magento\Framework\Registry $registry
	 */
	public function __construct(
		Registry $registry
	){
		$this->registry = $registry;	
	}
	
	/**
     * Get current product
     * @return mixed
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }
}