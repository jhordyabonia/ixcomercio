<?php
declare(strict_types=1);

namespace Intcomex\Bines\Model;

use Intcomex\Bines\Api\BinesRepositoryInterface;
use Intcomex\Bines\Api\Data\BinesInterfaceFactory;
use Intcomex\Bines\Api\Data\BinesSearchResultsInterfaceFactory;
use Intcomex\Bines\Model\ResourceModel\Bines as ResourceBines;
use Intcomex\Bines\Model\ResourceModel\Bines\CollectionFactory as BinesCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class BinesRepository implements BinesRepositoryInterface
{
    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    protected $dataBinesFactory;

    protected $binesFactory;

    protected $extensibleDataObjectConverter;

    protected $binesCollectionFactory;

    private $storeManager;

    private $collectionProcessor;

    protected $searchResultsFactory;

    protected $resource;

    protected $dataObjectHelper;

    /**
     * @param ResourceBines $resource
     * @param BinesFactory $binesFactory
     * @param BinesInterfaceFactory $dataBinesFactory
     * @param BinesCollectionFactory $binesCollectionFactory
     * @param BinesSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceBines $resource,
        BinesFactory $binesFactory,
        BinesInterfaceFactory $dataBinesFactory,
        BinesCollectionFactory $binesCollectionFactory,
        BinesSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->binesFactory = $binesFactory;
        $this->binesCollectionFactory = $binesCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBinesFactory = $dataBinesFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Intcomex\Bines\Api\Data\BinesInterface $bines
    ) {
        $binesData = $this->extensibleDataObjectConverter->toNestedArray(
            $bines,
            [],
            \Intcomex\Bines\Api\Data\BinesInterface::class
        );

        $binesModel = $this->binesFactory->create()->setData($binesData);

        try {
            $this->resource->save($binesModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the bines: %1',
                $exception->getMessage()
            ));
        }
        return $binesModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($entityId)
    {
        $bines = $this->binesFactory->create();
        $this->resource->load($bines, $entityId);
        if (!$bines->getId()) {
            throw new NoSuchEntityException(__('Bines with id "%1" does not exist.', $entityId));
        }
        return $bines->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->binesCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Intcomex\Bines\Api\Data\BinesInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Intcomex\Bines\Api\Data\BinesInterface $bines
    ) {
        try {
            $binesModel = $this->binesFactory->create();
            $this->resource->load($binesModel, $bines->getEntityId());
            $this->resource->delete($binesModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Bines: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->get($entityId));
    }
}
