<?php
/**
 * Aheadworks Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://ecommerce.aheadworks.com/end-user-license-agreement/
 *
 * @package    Popup
 * @version    1.2.3
 * @copyright  Copyright (c) 2020 Aheadworks Inc. (http://www.aheadworks.com)
 * @license    https://ecommerce.aheadworks.com/end-user-license-agreement/
 */
namespace Aheadworks\Popup\Block\Adminhtml;

/**
 * Class Popup
 * @package Aheadworks\Popup\Block\Adminhtml
 */
class Popup extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * Initialize object state with incoming parameters
     *
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'popup_admin';
        $this->_blockGroup = 'Aheadworks_Popup';
        $this->_headerText = __('Manage Popups');
        $this->_addButtonLabel = __('Create New Popup');
        parent::_construct();
    }
}
