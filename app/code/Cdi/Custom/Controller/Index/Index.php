<?php
namespace Cdi\Custom\Controller\Index;

use \Trax\Catalogo\Cron\GetCatalog;

class Index extends \Magento\Framework\App\Action\Action{
	
	protected $_pageFactory;
	protected $_cronCatalog;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Trax\Catalogo\Cron\GetCatalog $_cronCatalog
	){
		$this->_pageFactory = $pageFactory;
		$this->_cronCatalog = $_cronCatalog;
		return parent::__construct($context);
	}

	public function execute(){
		$this->_cronCatalog->execute();
		//return $this->_pageFactory->create();
	}
}

