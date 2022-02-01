<?php
declare(strict_types=1);

namespace Intcomex\Bines\Api;

interface BinesRepositoryInterface
{
    /**
     * Save Bines
     * @param \Intcomex\Bines\Api\Data\BinesInterface $bines
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Intcomex\Bines\Api\Data\BinesInterface $bines
    );

    /**
     * Retrieve Bines
     * @param string $entityId
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve Bines matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Intcomex\Bines\Api\Data\BinesSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Bines
     * @param \Intcomex\Bines\Api\Data\BinesInterface $bines
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Intcomex\Bines\Api\Data\BinesInterface $bines
    );

    /**
     * Delete Bines by ID
     * @param string $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);
}
