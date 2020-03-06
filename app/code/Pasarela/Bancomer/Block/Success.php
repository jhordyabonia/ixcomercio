<?php
namespace Pasarela\Bancomer\Block;
class Success extends \Magento\Framework\View\Element\Template
{
	protected $_orderCollectionFactory; # GTM add xcb

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context
		,\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory) # GTM add xcb
	{
		//$this->_orderCollectionFactory = $orderCollectionFactory; # GTM add xcb
		parent::__construct($context);
	}

	/*
	Add to class for get order details
	xcb GTM
	
	public function getOrderCollection(){
		$collection = $this->_orderCollectionFactory->create()
         ->addAttributeToSelect('*')
		 ->addFieldToFilter($field, $condition); //Add condition if you wish
		 return $collection;
	}
	*/
	/*
	Add to class for get order details
	xcb GTM 
	
	public function getOrderCollectionByCustomerId($customerId){
       $collection = $this->_orderCollectionFactory()->create($customerId)
         ->addFieldToSelect('*')
         ->addFieldToFilter('status',
                ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
            )
         ->setOrder(
                'created_at',
                'desc'
            );
 
     return $collection;

    }
	
	/*
	Add to class for get order details
	xcb GTM 
	
	public function getStoreName(){
		return $this->_storeManager->getStore()->getName();
	}*/
}