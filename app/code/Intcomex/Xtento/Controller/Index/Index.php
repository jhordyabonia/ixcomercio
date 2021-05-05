<?php

namespace Intcomex\Xtento\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;

class Index extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
	protected $_pageFactory;

    /**
    * @var RequestInterface
    */
    private $request;

	protected $_postFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Intcomex\Xtento\Model\XtxmlFactory $xtxmlFactory,
        RequestInterface $request
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->_xtxmlFactory = $xtxmlFactory;
        $this->request = $request;
		return parent::__construct($context);
	}

	public function execute()
	{
        $firstParam = $this->request->getParam('token', null);
        echo $firstParam;
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