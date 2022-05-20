<?php
declare(strict_types=1);

namespace Intcomex\Bines\Api\Data;

interface BinesInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const ENTITY_ID = 'entity_id';
    const CAMPAIGN = 'campaign';
    const BIN_CODES = 'bin_codes';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setEntityId($entityId);

    /**
     * Get campaign
     * @return string|null
     */
    public function getCampaign();

    /**
     * Set campaign
     * @param string $campaign
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setCampaign($campaign);

    /**
     * Get bin_codes
     * @return string|null
     */
    public function getBinCodes();

    /**
     * Set bin_codes
     * @param string $binCodes
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setBinCodes($binCodes);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setStatus($status);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Intcomex\Bines\Api\Data\BinesExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Intcomex\Bines\Api\Data\BinesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Intcomex\Bines\Api\Data\BinesExtensionInterface $extensionAttributes
    );
}
