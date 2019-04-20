<?php
namespace Cdi\Custom\Block\Adminhtml\Catalog\Product\Form;
use Cdi\Custom\Model\Images; 
use Cdi\Custom\Helper\Data;

class Attributes extends \Magento\Backend\Block\Template{
    
	/**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'group/product/attributes.phtml';
	
	public function getFieldsProduct(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
		$helper = $objectManager->get('Cdi\Custom\Helper\Data');
		$product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');
		$fields = array();
		$atts = $product->getProductAttributes();
		if($atts){
			$fields = $helper->getAttributeArrayFromJson($atts);
		}
		$fields[] = array(
			'type' => 'dummy',
		);
		/*
		echo '<pre>';
		var_dump($fields);
		die();
		*/
		return $fields;
	}
	
	public function getImages(){
		return Images::getAvailableImages();
	}
 
}