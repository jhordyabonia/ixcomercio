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
namespace Aheadworks\Popup\Controller\Adminhtml\Popup;

use Magento\Backend\App\Action;

/**
 * Class Save
 * @package Aheadworks\Popup\Controller\Adminhtml\Popup
 */
class ResetStatistic extends \Aheadworks\Popup\Controller\Adminhtml\Popup
{
    /**
     * Popup model factory
     * @var \Aheadworks\Popup\Model\PopupFactory
     */
    private $popupModelFactory;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param \Aheadworks\Popup\Model\PopupFactory $popupModelFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Aheadworks\Popup\Model\PopupFactory $popupModelFactory
    ) {
        $this->popupModelFactory = $popupModelFactory;
        parent::__construct($context);
    }

    /**
     * Reset statistic
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('popup_id', null);
        if ($id) {
            /* @var $ruleModel \Aheadworks\Popup\Model\Popup */
            $popupModel = $this->popupModelFactory->create();
            $popupModel->load($id);
            $popupModel->setViewCount(0);
            $popupModel->setClickCount(0);
            $popupModel->save();
        }
    }
}
