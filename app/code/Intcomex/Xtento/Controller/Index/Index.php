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
		$token = $this->getRequest()->getGetValue();
		print_r($token);
		$post = $this->_xtxmlFactory->create();
		$collection = $post->getCollection();
        header('Content-type: text/xml');
        $count = 0;
		foreach($collection as $key => $item){
            if($count==0){
                echo $item->getXml();
            }
            $count++;
        }
		exit();
		return $this->_pageFactory->create();
	}
}