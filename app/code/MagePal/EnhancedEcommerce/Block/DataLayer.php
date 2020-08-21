<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use MagePal\EnhancedEcommerce\Helper\Data;
use MagePal\GoogleTagManager\Block\DataLayerAbstract;
use MagePal\GoogleTagManager\Helper\Data as GtmHelper;

/**
 * Class DataLayer
 * @package MagePal\EnhancedEcommerce\Block
 */
class DataLayer extends DataLayerAbstract
{

    /**
     * @var string
     */
    protected $dataLayerEventName = 'magepal_ee_datalayer';

    /**
     * @var string
     */
    protected $_template = 'MagePal_EnhancedEcommerce::data_layer.phtml';

    /**
     * @var Data
     */
    protected $_eeHelper;

    /**
     * @var array $_impressionList
     s*/
    protected $_impressionList = [];

    /**
     * DataLayer constructor.
     * @param Context $context
     * @param GtmHelper $gtmHelper
     * @param Data $eeHelper
     * @param array $data
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        GtmHelper $gtmHelper,
        Data $eeHelper,
        array $data = []
    ) {
        $this->_eeHelper = $eeHelper;
        parent::__construct($context, $gtmHelper, $data);
    }

    /**
     * @return array
     */
    public function getImpressionList()
    {
        return $this->_impressionList;
    }

    /**
     * @return Json
     */
    public function getImpressionListJson()
    {
        return json_encode($this->getImpressionList());
    }

    /**
     * @return bool
     */
    public function hasImpressionList()
    {
        return (bool) !empty($this->getImpressionList());
    }

    /**
     * @param string $listType
     * @param string $className
     * @param $containerClass
     * @return DataLayer
     */
    public function setImpressionList($listType, $className, $containerClass)
    {
        $this->_impressionList[] = [
            'list_type' => $listType,
            'class_name' => $className,
            'container_class' => $containerClass
        ];

        return $this;
    }

    /**
     * @return $this
     */
    protected function _init()
    {
        return $this;
    }

    /**
     * Render tag manager script
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_eeHelper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Add category data to datalayer
     *
     * @return $this
     */
    protected function _dataLayer()
    {
        return $this;
    }

    /**
     * Return data layer json

     */
    public function getDataLayer()
    {
        if ($this->_eeHelper->isEnhancedEcommerceEnabled()) {
            $this->_dataLayer();
        }

        return parent::getDataLayer();
    }
}
