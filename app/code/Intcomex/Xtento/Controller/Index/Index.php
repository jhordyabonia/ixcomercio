<?php

namespace Intcomex\Xtento\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $_postFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Intcomex\Xtento\Model\XtxmlFactory $xtxmlFactory
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->_xtxmlFactory = $xtxmlFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$post = $this->_xtxmlFactory->create();
		$collection = $post->getCollection();
		foreach($collection as $item){
			echo "<pre>";
			print_r($item->getXml());
			echo "</pre>";
		}
		exit();
		return $this->_pageFactory->create();
	}
}