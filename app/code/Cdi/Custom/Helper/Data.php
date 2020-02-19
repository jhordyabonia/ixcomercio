<?php
namespace Cdi\Custom\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Filter\Truncate;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellersCollectionFactory;

class Data extends AbstractHelper{
 
	protected $pageFactory;
	protected $_scopeConfig;
    /**
     * @var TimezoneInterface
     */
	protected $localeDate;
	/**
     * @var Truncate
     */
    protected $filter;
    /**
     * @var BestSellersCollectionFactory
     */
    protected $_bestSellersCollectionFactory;
	
	public function __construct(PageFactory $pageFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	TimezoneInterface $localeDate, Truncate $filter,
	BestSellersCollectionFactory $bestSellersCollectionFactory){
		$this->pageFactory = $pageFactory;
		$this->_scopeConfig = $scopeConfig;
		$this->localeDate = $localeDate;	
		$this->filter = $filter;
        $this->_bestSellersCollectionFactory = $bestSellersCollectionFactory;	
	}
	
	/**
	* Corta un string en un límite de caracteres
	* @param string
	* @return string
	*/
	public function truncate($string, $length = 80, $etc = '...', $breakWords = true){
		$string = strip_tags($string);
		$getlength = strlen($string);
		if ($getlength > $length) {
			$string = substr($string, 0, strrpos($string, ' ', $length-$getlength));
			$string.= $etc;
		}
		return $string;
	}

	public function getStoreConfig($key){
		return $this->_scopeConfig->getValue($key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
	
	public function getWeightUnit(){
		return $this->_scopeConfig->getValue(
			'general/locale/weight_unit',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
    }
	
	public function getMeasureUnit($weight){
		switch($weight){
			case 'lbs':
				return 'in';
			default: 
				return 'cms';
		}
	}
	
	public function customerHasPassword($cid){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();  
        $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
		$customer = $customerFactory->load($cid);
		return $customer->getPasswordHash();
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
	
	public function getDiscount($_product){ 
		switch($_product->getTypeId()){
			case 'configurable':
				$basePrice = $_product->getPriceInfo()->getPrice('regular_price');
				$regularPrice = $basePrice->getMinRegularAmount()->getValue();
				$specialPrice = $_product->getFinalPrice();
				break;
			case 'bundle':
				$regularPrice = $_product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
				$specialPrice = $_product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
				break;
			case 'grouped':
				$usedProds = $_product->getTypeInstance(true)->getAssociatedProducts($_product);            
				foreach ($usedProds as $child) {
					if ($child->getId() != $_product->getId()) {
						$regularPrice += $child->getPrice();
						$specialPrice += $child->getFinalPrice();
					}
				}
				break;
			default:
				$regularPrice = $_product->getPriceInfo()->getPrice('regular_price')->getValue();
				$specialPrice = $_product->getPriceInfo()->getPrice('special_price')->getValue();
		}  
		if($regularPrice != $specialPrice){
			$discount = ($specialPrice * 100) / $regularPrice;
			return round($discount);
		}
		return 0;
	}
	
	public function isNew ($_product){
        $newsFromDate = $_product->getNewsFromDate();
        $newsToDate = $_product->getNewsToDate();
        if (!$newsFromDate) {
            return false;
        }

        return true;
    }

    /**
     * get collection of best-seller products
     * @return mixed
     */
    public function checkBestseller($_product)
    {
        $bestSellers = $this->_bestSellersCollectionFactory->create()
            ->setPeriod('month');

        foreach ($bestSellers as $product) {
			if($product->getProductId() == $_product->getId()){
				return true;
			}
        }

		return false;
    }
 
}