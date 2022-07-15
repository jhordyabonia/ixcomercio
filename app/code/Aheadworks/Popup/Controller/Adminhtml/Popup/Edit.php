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
 * Class Edit
 * @package Aheadworks\Popup\Controller\Adminhtml\Popup
 */
class Edit extends \Aheadworks\Popup\Controller\Adminhtml\Popup
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * Popup model factory
     *
     * @var \Aheadworks\Popup\Model\PopupFactory
     */
    private $popupModelFactory;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Popup\Model\PopupFactory $popupModelFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Popup\Model\PopupFactory $popupModelFactory
    ) {
        $this->coreRegistry = $registry;
        $this->popupModelFactory = $popupModelFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Edit Popup
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /* @var $popupModel \Aheadworks\Popup\Model\Popup */
        $popupModel = $this->popupModelFactory->create();

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $popupModel->load($id);
            if (!$popupModel->getId()) {
                $this->messageManager->addErrorMessage(__('This popup no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/*');
            }
        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $popupModel->setData($data);
        }
        $this->coreRegistry->register('aw_popup_model', $popupModel);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Aheadworks_Popup::popup');
        $resultPage->getConfig()->getTitle()->prepend(
            $popupModel->getId() ? sprintf("%s \"%s\"", __('Edit Popup'), $popupModel->getName()) : __('New Popup')
        );
        $resultPage->getLayout()->getBlock('aw_popup.menu')->setCurrentItemKey(
            \Aheadworks\Popup\Block\Adminhtml\Menu::ITEM_BLOCK
        );
        return $resultPage;
    }
}
