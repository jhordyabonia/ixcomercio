<?php

namespace Intcomex\Xtento\Controller\Export;

class Export extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $request;
	protected $_xtxmlFactory;
	protected $collection;
	protected $resourceConnection;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Intcomex\Xtento\Model\XtxmlFactory $xtxmlFactory,
		\Intcomex\Xtento\Model\ResourceModel\Xtxml\CollectionFactory $CollectionFactory,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\App\ResourceConnection $resourceConnection
		)
	{
		$this->resourceConnection = $resourceConnection;
		$this->_pageFactory = $pageFactory;
		$this->_xtxmlFactory = $xtxmlFactory;
		$this->request = $request;
		$this->collection = $CollectionFactory;
		return parent::__construct($context);
	}

	/**
	* Get Table name using direct query
	*/
	public function getTablename($tableName)
	{

        /* Create Connection */

        $connection  = $this->resourceConnection->getConnection();

        $tableName   = $connection->getTableName($tableName);

        return $tableName;

    }

	public function execute()
	{
		$token = $this->request->getParam('token');
        $post = $this->_xtxmlFactory->create();
				$collection = $post->getCollection()->addFieldToFilter('token', array('eq' => $token))->setOrder('fecha','DESC')->setPageSize(1)->getData();
				header('Content-type: text/xml');
        print($collection[0]['xml']);
        exit();
		return $this->_pageFactory->create();
	}
}