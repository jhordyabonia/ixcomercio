<?php
declare(strict_types=1);

namespace Intcomex\Bines\Controller\Adminhtml;

abstract class Bines extends \Magento\Backend\App\Action
{
    /**
     * @const ADMIN_RESOURCE
     */
    const ADMIN_RESOURCE = 'Intcomex_Bines::Bines';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Intcomex'), __('Intcomex'))
            ->addBreadcrumb(__('Bines'), __('Bines'));
        return $resultPage;
    }
}
