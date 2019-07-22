<?php

namespace Pasarela\Bancomer\Model;

class BancomerCustomer extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

    const CACHE_TAG = 'bancomer_customers';

    protected $_cacheTag = 'bancomer_customers';
    protected $_eventPrefix = 'bancomer_customers';
        
    protected function _construct() {
        $this->_init('Pasarela\Bancomer\Model\ResourceModel\BancomerCustomer');        
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues() {
        $values = [];

        return $values;
    }
    
    public function fetchOneBy($field, $value) {        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();                
        $tableName = $connection->getTableName('bancomer_customers'); //gives table name with prefix        
        
        $sql = 'Select * FROM '.$tableName.' WHERE '.$field.' = "'.$value.'" limit 1';        
        $result = $connection->fetchAll($sql);
        
        if (count($result)) {
            return json_decode(json_encode($result[0]), false);
        }
        
        return false;
    }


    /**
     * {@inheritDoc}
     */
    public function setBancomerId($openpayId)
    {
        return $this->setData('bancomer_id', $openpayId);
    }

    /**
     * {@inheritDoc}
     */
    public function getBancomerId()
    {
        return $this->getData('bancomer_id');
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData('customer_id', $customerId);
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

}
