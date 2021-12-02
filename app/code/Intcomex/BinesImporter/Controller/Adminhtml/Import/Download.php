<?php
declare(strict_types=1);

namespace Intcomex\BinesImporter\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\ImportExport\Controller\Adminhtml\Import\Download as MagentoDownload;

class Download extends MagentoDownload
{
    /**
     * @var Reader
     */
    protected $moduleReader;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param RawFactory $resultRawFactory
     * @param ReadFactory $readFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param Reader $moduleReader
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        RawFactory $resultRawFactory,
        ReadFactory $readFactory,
        ComponentRegistrar $componentRegistrar,
        Reader $moduleReader
    ) {
        parent::__construct(
            $context,
            $fileFactory,
            $resultRawFactory,
            $readFactory,
            $componentRegistrar
        );
        $this->moduleReader = $moduleReader;
    }

    /**
     * Get CSV's directory.
     *
     * @return string
     */
    public function getDirectory(): string
    {
        $viewDir = $this->moduleReader->getModuleDir(
            Dir::MODULE_VIEW_DIR,
            'Intcomex_BinesImporter'
        );
        return $viewDir . '/adminhtml/Files/Sample/';
    }

    /**
     * @return Raw|Redirect
     * @throws Exception
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function execute()
    {
        $fileName = $this->getRequest()->getParam('filename') . '.csv';
        $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::SAMPLE_FILES_MODULE);
        $fileAbsolutePath = $moduleDir . '/Files/Sample/' . $fileName;
        $directoryRead = $this->readFactory->create($moduleDir);
        $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

        if (!$directoryRead->isFile($filePath)) {
            $fileAbsolutePath = $this->getDirectory() . $fileName;
            $directoryRead = $this->readFactory->create($this->getDirectory());
            $filePath = $directoryRead->getRelativePath($fileAbsolutePath);
            if (!$directoryRead->isFile($filePath)) {
                /** @var Redirect $resultRedirect */
                $this->messageManager->addError(__('There is no sample file for this entity.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/import');
                return $resultRedirect;
            }
        }

        $fileSize = $directoryRead->stat($filePath)['size'] ?? null;
        $this->fileFactory->create(
            $fileName,
            null,
            DirectoryList::VAR_DIR,
            'application/octet-stream',
            $fileSize
        );

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($directoryRead->readFile($filePath));
        return $resultRaw;
    }
}
