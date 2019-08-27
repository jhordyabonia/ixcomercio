<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Model\Ticket;

use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }
}
