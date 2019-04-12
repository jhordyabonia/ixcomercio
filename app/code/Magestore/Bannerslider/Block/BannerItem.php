<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Bannerslider
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Bannerslider\Block;

use Magestore\Bannerslider\Model\Banner as BannerModel;
use Magestore\Bannerslider\Model\Type;
use Magestore\Bannerslider\Model\Status;

class BannerItem extends \Magento\Framework\View\Element\Template{
    /**
     * template for image.
     */
    const BANNER_IMAGE_TEMPLATE = 'Magestore_Bannerslider::banner/image.phtml';

    /**
     * template for video.
     */
    const BANNER_VIDEO_TEMPLATE = 'Magestore_Bannerslider::banner/video.phtml';

    /**
     * Date conversion model.
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
    protected $_stdlibDateTime;
     */

    /**
     * slider factory.
     *
     * @var \Magestore\Bannerslider\Model\SliderFactory
    protected $_bannerFactory;
     */

    /**
     * slider model.
     *
     * @var \Magestore\Bannerslider\Model\Slider
     */
    protected $_banner;

    /**
     * slider id.
     *
     * @var int
     */
    protected $_bannerId;

    /**
     * banner slider helper.
     *
     * @var \Magestore\Bannerslider\Helper\Data
     */
    protected $_bannersliderHelper;

    /**
     * @var \Magestore\Bannerslider\Model\ResourceModel\Banner\CollectionFactory
    protected $_bannerCollectionFactory;
     */

    /**
     * scope config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
    protected $_scopeConfig;
     */

    /**
     * stdlib timezone.
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
    protected $_stdTimezone;
     */

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magestore\Bannerslider\Helper\Data $bannersliderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
		$this->_bannersliderHelper = $bannersliderHelper;
    }
	
	 /**
     * set slider Id and set template.
     *
     * @param int $bannerId
     */
    public function setBannerId($bannerId){
        $this->_bannerId = $bannerId;
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$banner = $objectManager->create('\Magestore\Bannerslider\Model\Banner')->load($this->_bannerId);
        if($banner->getId()){
            $this->setBanner($banner);
            if($banner->getBannerType() == Type::TYPE_IMAGE) {
                $this->setTemplate(self::BANNER_IMAGE_TEMPLATE);
            } else {
				$this->setTemplate(self::BANNER_VIDEO_TEMPLATE);
            }
        }
        return $this;
    }
	
	/**
     * set banner model.
     *
     * @param \Magestore\Bannerslider\Model\Banner $banner [description]
     */
    public function setBanner(\Magestore\Bannerslider\Model\Banner $banner){
        $this->_banner = $banner;
        return $this;
    }
	
	
    /**
     * @return \Magestore\Bannerslider\Model\Banner
     */
    public function getBanner(){
        return $this->_banner;
    }


    /**
     * @return
     */
    protected function _toHtml(){
        if($this->_banner->getStatus() === Status::STATUS_DISABLED || !$this->_banner->getId()){
            return '';
        }
        return parent::_toHtml();
    }
	
	
    /**
     * get banner image url.
     *
     * @param \Magestore\Bannerslider\Model\Banner $banner
     *
     * @return string
     */
    public function getBannerImageUrl(\Magestore\Bannerslider\Model\Banner $banner){
        return $this->_bannersliderHelper->getBaseUrlMedia($banner->getImage());
    }
	
	/*
	* 
	*/
	public function getBannerId(){
        return $this->_bannerId;
    }
   
    /**
     * set style slide template.
     *
     * @param int $styleSlideId
     *
     * @return string
     */

    public function isShowTitle()
    {
        return $this->_slider->getShowTitle() == SliderModel::SHOW_TITLE_YES ? TRUE : FALSE;
    }

    /**
     * get banner collection of slider.
     *
     * @return \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection
     */
    public function getBannerCollection()
    {
        $sliderId = $this->_slider->getId();
        return $this->_bannerCollectionFactory->getBannerCollection($sliderId);
    }

    /**
     * get first banner.
     *
     * @return \Magestore\Bannerslider\Model\Banner
     */
    public function getFirstBannerItem()
    {
        return $this->getBannerCollection()
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();
    }

    /**
     * get position note.
     *
     * @return string
     */
    public function getPositionNote()
    {
        return $this->_slider->getPositionNoteCode();
    }

    /**
     * get flexslider html id.
     *
     * @return string
     */
    public function getFlexsliderHtmlId()
    {
        return 'magestore-bannerslider-flex-slider-'.$this->getSlider()->getId().$this->_stdlibDateTime->gmtTimestamp();
    }
}
