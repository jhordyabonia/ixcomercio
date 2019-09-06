<?php

/**
 * Conciliacion Admin Cagegory Map Record Loadfile Controller.
 * @category  Pasarela
 * @package   Pasarela_Conciliacion
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2016 Pasarela Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Pasarela\Conciliacion\Controller\Adminhtml\Conciliacion;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use \Psr\Log\LoggerInterface;
 
class Loadfile extends Action
{
    protected $fileSystem;
 
    protected $uploaderFactory;
 
    protected $allowedExtensions = ['des', 'xls', 'xlsx']; // to allow file upload types 
 
    protected $fileId = 'conciliation_file'; // name of the input file box  
 
    public function __construct(
        LoggerInterface $logger,
        Action\Context $context,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory
    ) {
        $this->logger = $logger;
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $destinationPath = $this->getDestinationPath();
 
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $this->fileId])
                ->setAllowCreateFolders(true)
                ->setAllowedExtensions($this->allowedExtensions);
            if (!$uploader->save($destinationPath)) {
                throw new LocalizedException(
                    __('File cannot be saved to path: $1', $destinationPath)
                );
                $this->logger->info('BANCOMER - Error al cargar el archivo');
            } else {
                $this->logger->info('BANCOMER - Se carga el archivo');
            }
 
            // @todo
            // process the uploaded file
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __($e->getMessage())
            );
        }
        $this->_redirect('pasarela_conciliacion/conciliacion/addrow');
        return;
    }
    
    public function validateFile($filePath)
    {
        // @todo
        // your custom validation code here
    }
 
    public function getDestinationPath()
    {
        return $this->fileSystem
            ->getDirectoryWrite(DirectoryList::TMP)
            ->getAbsolutePath('/');
    }
}