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

use Aheadworks\Popup\Model\Source\PageType;
use Magento\Backend\App\Action;

/**
 * Class Save
 * @package Aheadworks\Popup\Controller\Adminhtml\Popup
 */
class Save extends \Aheadworks\Popup\Controller\Adminhtml\Popup
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
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /* @var $ruleModel \Aheadworks\Popup\Model\Popup */
            $popupModel = $this->popupModelFactory->create();

            $id = $this->getRequest()->getParam('id');
            if ($this->getRequest()->getParam('back') == 'new') {
                unset($data['id']);
                $id = null;
            }

            if ($id) {
                $popupModel->load($id);
            }

            /* check page and remove excess data*/
            if (false === array_search(PageType::PRODUCT_PAGE, $data['page_type'])) {
                $data['rule'] = '';
            }

            if (false === array_search(PageType::CATEGORY_PAGE, $data['page_type'])) {
                $data['category_ids'] = '';
            }

            $popupModel->setData($data);

            try {
                $rule = $data['rule'];
                $popupModel->loadPost($rule, ['popup']);

                $popupModel->save();

                $this->messageManager->addSuccessMessage(__('Popup was successfully saved'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/popup/edit', ['id' => $popupModel->getId()]);
                }
                return $resultRedirect->setPath('*/popup/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the popup.'));
            }
            $data['id'] = $id;
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $resultRedirect->setPath('*/popup/');
    }
}
