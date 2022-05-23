<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_SeoToolbar
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoToolbar\Controller\Adminhtml\Edit;

class Redirect extends \Magento\Backend\App\Action
{
    /**
     * @var array
     */
    protected $_publicActions = ['redirect'];

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        $typeId = $this->getRequest()->getParam('entityId');
        if ($type == 'category') {
            $redirectUrl = $this->_helper->getUrl('catalog/category/edit', ['id' => $typeId]);
        } elseif ($type == 'cms-page') {
            $redirectUrl = $this->_helper->getUrl('cms/page/edit', ['page_id' => $typeId]);
        } elseif ($type == 'product') {
            $redirectUrl = $this->_helper->getUrl('catalog/product/edit', ['id' => $typeId]);
        } else {
            $redirectUrl = $this->_helper->getUrl('admin/dashboard');
        }
        $this->getResponse()->setRedirect($redirectUrl);
    }
}
