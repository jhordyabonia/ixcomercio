<?php
declare(strict_types=1);

namespace Intcomex\Bines\Model\Data;

use Intcomex\Bines\Api\Data\BinesInterface;

class Bines extends \Magento\Framework\Api\AbstractExtensibleObject implements BinesInterface
{
    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get campaign
     * @return string|null
     */
    public function getCampaign()
    {
        return $this->_get(self::CAMPAIGN);
    }

    /**
     * Set campaign
     * @param string $campaign
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setCampaign($campaign)
    {
        return $this->setData(self::CAMPAIGN, $campaign);
    }

    /**
     * Get bin_codes
     * @return string|null
     */
    public function getBinCodes()
    {
        return $this->_get(self::BIN_CODES);
    }

    /**
     * Set bin_codes
     * @param string $binCodes
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setBinCodes($binCodes)
    {
        return $this->setData(self::BIN_CODES, $binCodes);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Intcomex\Bines\Api\Data\BinesInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Intcomex\Bines\Api\Data\BinesExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Intcomex\Bines\Api\Data\BinesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Intcomex\Bines\Api\Data\BinesExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
