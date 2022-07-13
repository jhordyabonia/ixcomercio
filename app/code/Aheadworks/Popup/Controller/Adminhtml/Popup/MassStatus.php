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
use Magento\Catalog\Controller\Adminhtml\Product;

/**
 * Class MassStatus
 * @package Aheadworks\Popup\Controller\Adminhtml\Popup
 */
class MassStatus extends \Aheadworks\Popup\Controller\Adminhtml\Popup
{

    /**
     * Popup Collection
     * @var \Aheadworks\Popup\Model\ResourceModel\Popup\Collection
     */
    protected $collection;

    /**
     * Massaction filter
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

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
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Aheadworks\Popup\Model\PopupFactory $popupModelFactory
     * @param \Aheadworks\Popup\Model\ResourceModel\Popup\Collection $collection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Aheadworks\Popup\Model\PopupFactory $popupModelFactory,
        \Aheadworks\Popup\Model\ResourceModel\Popup\Collection $collection
    ) {
        parent::__construct($context);
        $this->collection = $collection;
        $this->filter = $filter;
        $this->popupModelFactory = $popupModelFactory;
    }

    /**
     * Mass update popup(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->collection = $this->filter->getCollection($this->collection);
        $status = (int) $this->getRequest()->getParam('status');
        $count = 0;

        foreach ($this->collection->getItems() as $popup) {
            $popupModel = $this->popupModelFactory->create();
            $popupModel->load($popup->getId());
            if ($popupModel->getId()) {
                $popupModel->setStatus($status);
                $popupModel->save();
                $count++;
            }
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been updated.', $count)
        );
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
