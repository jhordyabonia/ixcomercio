<?php

namespace Intcomex\Xtento\Controller\Xml;

class Export extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $request;
	protected $_postFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Intcomex\Xtento\Model\XtxmlFactory $xtxmlFactory,
		\Magento\Framework\App\Request\Http $request
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->_xtxmlFactory = $xtxmlFactory;
		$this->request = $request;
		return parent::__construct($context);
	}

	public function execute()
	{
		$token = $this->request->getParam('token');
		echo "Se imprime token";
		echo $token;
		die();
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