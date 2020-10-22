<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cdi\Custom\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\ImportExport\Model\Export as ExportModel;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\ImportExport\Model\Export\Entity\ExportInfoFactory;

/**
 * Controller for export operation.
 */
class Export extends ExportController implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * @var ExportInfoFactory
     */
    private $exportInfoFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param \Magento\Framework\Session\SessionManagerInterface|null $sessionManager
     * @param PublisherInterface|null $publisher
     * @param ExportInfoFactory|null $exportInfoFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager = null,
        PublisherInterface $publisher = null,
        ExportInfoFactory $exportInfoFactory = null
    ) {
        $this->fileFactory = $fileFactory;
        $this->sessionManager = $sessionManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Session\SessionManagerInterface::class);
        $this->messagePublisher = $publisher ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(PublisherInterface::class);
        $this->exportInfoFactory = $exportInfoFactory ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                ExportInfoFactory::class
            );
        parent::__construct($context);
    }

    /**
     * Load data with filter applying and create file for download.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->getPost(ExportModel::FILTER_ELEMENT_GROUP)) {
            try {
                 $model = $this->_objectManager->create(\Magento\ImportExport\Model\Export::class);
                 $model->setData($this->getRequest()->getParams());
                 $this->sessionManager->writeClose();
                return $this->fileFactory->create(
                  $model->getFileName(),
                  $model->export(),
                  \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                  $model->getContentType()
                 );
               
            } catch (\Exception $e) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->messageManager->addError(__('Please correct the data sent value.'));
            }
        } else {
            $this->messageManager->addError(__('Please correct the data sent value.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }

    
}
