<?php
declare(strict_types=1);

namespace Intcomex\Bines\Model;

use Intcomex\Bines\Api\Data\BinesInterface;
use Intcomex\Bines\Api\Data\BinesInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Bines extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string $_eventPrefix
     */
    protected $_eventPrefix = 'intcomex_bines';

    /**
     * @var BinesInterfaceFactory
     */
    protected $binesDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param BinesInterfaceFactory $binesDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Intcomex\Bines\Model\ResourceModel\Bines $resource
     * @param \Intcomex\Bines\Model\ResourceModel\Bines\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        BinesInterfaceFactory $binesDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Intcomex\Bines\Model\ResourceModel\Bines $resource,
        \Intcomex\Bines\Model\ResourceModel\Bines\Collection $resourceCollection,
        array $data = []
    ) {
        $this->binesDataFactory = $binesDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve bines model with bines data
     * @return BinesInterface
     */
    public function getDataModel()
    {
        $binesData = $this->getData();

        $binesDataObject = $this->binesDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $binesDataObject,
            $binesData,
            BinesInterface::class
        );

        return $binesDataObject;
    }
}
