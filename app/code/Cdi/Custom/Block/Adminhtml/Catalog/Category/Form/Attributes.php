<?php
namespace Cdi\Custom\Block\Adminhtml\Catalog\Category\Form;
use Cdi\Custom\Model\Images; 
use Cdi\Custom\Helper\Data;

class Attributes extends \Magento\Backend\Block\Template{
    
	/**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'group/attributes.phtml';
	
	public function getFieldsCategory(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
		$helper = $objectManager->get('Cdi\Custom\Helper\Data');
		$category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');
		$fields = array();
		$atts = $category->getCatAttributes();
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