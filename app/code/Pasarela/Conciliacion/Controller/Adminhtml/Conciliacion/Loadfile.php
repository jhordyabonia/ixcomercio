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
 
    protected $allowedExtensions = ['csv']; // to allow file upload types 
 
    protected $fileId = 'conciliation_file'; // name of the input file box  
 
    public function __construct(
        LoggerInterface $logger,
        Action\Context $context,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
        \Magento\Framework\File\Csv $csv
    ) {
        $this->logger = $logger;
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->csv = $csv;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $destinationPath = $this->getDestinationPath();
 
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $this->fileId])
                ->setAllowCreateFolders(true)
                ->setAllowedExtensions($this->allowedExtensions);
            $result = $uploader->save($destinationPath);   
            if (!$result) {
                $this->messageManager->addError(
                    __('File cannot be saved to path: '.$destinationPath)
                );
                $this->logger->info('BANCOMER - Error al cargar el archivo en la ruta: '.$destinationPath);
            } else {
                $this->logger->info('BANCOMER - Se carga el archivo: '.$this->getFilePath($destinationPath, $result['file']));
                $this->validateFile($this->getFilePath($destinationPath, $result['file']));
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
        $this->logger->info('BANCOMER - entra a función: '.$filePath);
        $fila = 1;
        if (($gestor = fopen($filePath, "r")) !== FALSE) {
            while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                $numero = count($datos);
                $this->logger->info('BANCOMER - '.$numero.' de campos en la línea '.$fila);
                $fila++;
                for ($c=0; $c < $numero; $c++) {
                    $this->logger->info('BANCOMER - Datos: '.$datos[$c]);
                }
            }
            fclose($gestor);
        }
    }
 
    public function getDestinationPath()
    {
        return $this->fileSystem
            ->getDirectoryWrite(DirectoryList::TMP)
            ->getAbsolutePath('/');
    }

    public function getFilePath($path, $fileName)
    {
        return rtrim($path, '/') . '/' . ltrim($fileName, '/');
    }
}