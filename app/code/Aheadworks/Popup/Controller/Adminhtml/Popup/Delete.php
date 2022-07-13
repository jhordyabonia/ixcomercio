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

/**
 * Class Delete
 * @package Aheadworks\Popup\Controller\Adminhtml\Popup
 */
class Delete extends \Aheadworks\Popup\Controller\Adminhtml\Popup
{
    /**
     * Popup model factory
     * @var \Aheadworks\Popup\Model\PopupFactory
     */
    private $popupModelFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Aheadworks\Popup\Model\PopupFactory $popupModelFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Aheadworks\Popup\Model\PopupFactory $popupModelFactory
    ) {
        parent::__construct($context);
        $this->popupModelFactory = $popupModelFactory;
    }

    /**
     * Delete Popup
     *
     * @return $this
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        /* @var $popupModel \Aheadworks\Popup\Model\Popup */
        $popupModel = $this->popupModelFactory->create();
        if ($id) {
            $popupModel->load($id);
            if ($popupModel->getId()) {
                try {
                    $popupModel->delete();
                    $this->messageManager->addSuccessMessage(__('Popup was successfully deleted.'));
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('*/popup/edit', ['id' => $this->getRequest()->getParam('id')]);
                }
            }
        }
        return $resultRedirect->setPath('*/popup/');
    }
}
