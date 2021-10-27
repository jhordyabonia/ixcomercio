<?php
declare(strict_types=1);

namespace Intcomex\Bines\Api\Data;

interface BinesSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Bines list.
     * @return \Intcomex\Bines\Api\Data\BinesInterface[]
     */
    public function getItems();

    /**
     * Set bin_code list.
     * @param \Intcomex\Bines\Api\Data\BinesInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
