<?php

namespace Intcomex\Xtento\Controller\Export;

class Export extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $request;
	protected $_xtxmlFactory;
	protected $collection;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Intcomex\Xtento\Model\XtxmlFactory $xtxmlFactory,
		\Intcomex\Xtento\Model\ResourceModel\Xtxml\CollectionFactory $CollectionFactory,
		\Magento\Framework\App\Request\Http $request
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->_xtxmlFactory = $xtxmlFactory;
		$this->request = $request;
		$this->collection = $CollectionFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$token = $this->request->getParam('token');
		$post = $this->_xtxmlFactory->create();
		$collection = $post->getCollection()->addAttributeToFilter('token', array('eq'=>'12345'));;
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