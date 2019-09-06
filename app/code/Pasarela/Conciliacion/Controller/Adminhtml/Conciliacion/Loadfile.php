<?php

/**
 * Conciliacion Admin Cagegory Map Record Loadfile Controller.
 * @category  Pasarela
 * @package   Pasarela_Conciliacion
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2016 Pasarela Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Pasarela\Conciliacion\Controller\Adminhtml\Conciliacion;use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;  

class Loadfile extends \Vendor\Blog\Controller\Adminhtml\Blog
{

    protected $_fileUploaderFactory;
    protected $_directory_list;
    protected $_logger;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_directory_list = $directory_list;
        $this->_logger = $logger;
        parent::__construct($context, $coreRegistry);
    }

    public function execute(){
        $uploader = $this->_fileUploaderFactory->create(['fileId' => 'featured_images']);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('blog');
        //$path = $this->_directory_list->getPath('media') . '/blog';
        $this->_logger->debug('Uploader.php: '.$path);
        $uploader->save($path);
    }
}
