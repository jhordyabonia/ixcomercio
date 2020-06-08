<?php
namespace Cdi\Custom\Controller\Index;

use \Trax\Catalogo\Cron\GetCatalog;
use \Trax\Catalogo\Cron\OrderStatus;

class Index extends \Magento\Framework\App\Action\Action{
	
	protected $_pageFactory;
	protected $_getCatalog;
	protected $_orderStatus;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $_pageFactory,
		\Trax\Catalogo\Cron\GetCatalog $_getCatalog,
		\Trax\Catalogo\Cron\OrderStatus $_orderStatus

	){
		$this->_pageFactory = $_pageFactory;
		$this->_getCatalog = $_getCatalog;
		$this->_orderStatus = $_orderStatus;
		return parent::__construct($context);
	}

	public function execute(){
		//$this->_getCatalog->execute();
		$this->_orderStatus->execute();
		//return $this->_pageFactory->create();
	}
}

