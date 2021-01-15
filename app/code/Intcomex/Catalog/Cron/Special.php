<?php

namespace Intcomex\Catalog\Cron;

class Special
{

    protected $productCollectionFactory;

    protected $categoryFactory;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria,
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Store\Api\StoreRepositoryInterface $storesRepository
    ) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/SpecialPriceDate.log');
		$logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $this->productRepository = $productRepository;
        $this->searchCriteria = $criteria;
        $this->filterGroup = $filterGroup;
        $this->filterBuilder = $filterBuilder;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->_storesRepository = $storesRepository;
    }

	public function execute()
	{       
        /*
        $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        $storeManager=$objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeScope=\Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        foreach($this->_storesRepository->getList() as $store){

            if ($store->isActive()) {
            
                $websiteId=$storeManager->getStore($store->getId())->getWebsiteId();
                $website=$storeManager->getWebsite($websiteId);
                $products = $this->getProductData();
                $this->logger->info('Special - El website ' . $website->getCode() . ' con store ' . $website->getCode());
                $this->logger->info("PRODUCTS\n".$products);
            }
        }*/
        $products = $this->getProductData();
        $this->logger->info("PRODUCTS\n".$products);
        return $this;
    }
    
    protected function getProductData()
    {

        $this->filterGroup->setFilters([
            $this->filterBuilder
                ->setField('status')
                ->setConditionType('in')
                ->setValue($this->productStatus->getVisibleStatusIds())
                ->create(),
            $this->filterBuilder
                ->setField('visibility')
                ->setConditionType('in')
                ->setValue($this->productVisibility->getVisibleInSiteIds())
                ->create(),
        ]);

        $this->searchCriteria->setFilterGroups([$this->filterGroup]);
        $products = $this->productRepository->getList($this->searchCriteria);
        $productItems = $products->getItems();

        return $productItems;
    }
}