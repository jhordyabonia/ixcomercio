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

use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Class NewConditionHtml
 * @package Aheadworks\Popup\Controller\Adminhtml\Popup
 */
class NewConditionHtml extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * Create new condition
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $prefix = 'conditions';
        if ($this->getRequest()->getParam('prefix')) {
            $prefix = $this->getRequest()->getParam('prefix');
        }

        $rule = \Aheadworks\Popup\Model\Popup::class;
        if ($this->getRequest()->getParam('rule')) {
            // phpcs:disable Magento2.Functions
            $rule = base64_decode($this->getRequest()->getParam('rule'));
        }
        $model = $this->_objectManager->create(
            $type
        )->setId(
            $id
        )->setType(
            $type
        )->setRule(
            $this->_objectManager->create($rule)
        )->setPrefix(
            $prefix
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Check if page is allowed
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aheadworks_Popup::popup');
    }
}
