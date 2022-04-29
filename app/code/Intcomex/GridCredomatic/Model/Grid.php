<?php


namespace Intcomex\GridCredomatic\Model;

use Intcomex\GridCredomatic\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'transacciones_credomatic';

    /**
     * @var string
     */
    protected $_cacheTag = 'transacciones_credomatic';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'transacciones_credomatic';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Intcomex\GridCredomatic\Model\ResourceModel\Grid');
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ID);
    }


    /**
     * Get Title.
     *
     * @return varchar
     */
    public function getResponse()
    {
        return $this->getData(self::RESPONSE);
    }

   
    /**
     * Get getContent.
     *
     * @return varchar
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set Content.
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Get UpdatedAt.
     *
     * @return varchar
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Get Token.
     *
     * @return varchar
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

  
}
