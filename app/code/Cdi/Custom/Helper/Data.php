<?php
namespace Cdi\Custom\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Cms\Model\PageFactory;

class Data extends AbstractHelper{
 
	protected $pageFactory;
	protected $_scopeConfig;
	
	public function __construct(PageFactory $pageFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig){
		$this->pageFactory = $pageFactory;
		$this->_scopeConfig = $scopeConfig;
		
	}
	
	public function getStoreConfig($key){
		return $this->_scopeConfig->getValue($key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
	
	public function getWeightUnit(){
		return $this->_scopeConfig->getValue(
			'general/locale/weight_unit',
			ScopeInterface::SCOPE_STORE
		);
    }
	
	public function getMeasureUnit($weight){
		return $this->_scopeConfig->getValue(
			'general/locale/weight_unit',
			ScopeInterface::SCOPE_STORE
		);
    }
	
    public function getAttributeArrayFromJson($json){
		$fields = array();
        $atss = json_decode($json);
		$data = array();
		if(is_array($atss)){
			foreach($atss as $val){
				//inicia un nuevo array
				if($val->name == 'title'){
					if(!empty($data)) $fields[] = $data;
					$data = array('type' => 'data');
				}
				$data[$val->name] = $val->value;
			}
			$fields[] = $data;
		}
		return $fields;
    }
	
	public function getPage($identifier){
		$page = $this->pageFactory->create()->load($identifier);
		if($page){
			return $page->getTitle();
		}
		return false;
	}
	
	public function getCustomProductImage($_product, $attribute){
		$productImageAttr = $_product->getCustomAttribute($attribute);
		if($productImageAttr && $productImageAttr->getValue() && 'no_selection' != $productImageAttr->getValue()){
			return $productImageAttr->getValue();
		}
		return false;
	}
	
	public function getProductGalleryImages($_product){
		$images = $_product->getMediaGalleryImages();
		if($images->count()>0){
			$i = 0;
			foreach($images as $child){
				$i++;
				return $child;
			}
		}
		return false;
	}
	
	public function checkUrl($string){
		if(!$string) return '';
		if(filter_var($string, FILTER_VALIDATE_URL)){
			return $string;
		}
		return "/{$string}";
	}
	
	public function getColumnsBt($count){
		switch($count){
			case 1:
			case 2:
			case 3:
			case 4:
			case 6:
				return 12/$count;
			case 5:
				return 2;
			default:
				return 1;
		}
	}
 
}