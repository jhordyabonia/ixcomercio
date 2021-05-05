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

		$tableName = $this->getTableName('xtento_catalog_xml');
		$query = "SELECT * FROM " . 
							$tableName .
							" WHERE token = ".
							$token;
		/**
		* Execute the query and store the results in $results variable
		*/
		$results = $this->resourceConnection->getConnection()->fetchAll($query);
		echo "<pre>";print_r($results);
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