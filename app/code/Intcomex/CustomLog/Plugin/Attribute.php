<?php

namespace Intcomex\CustomLog\Plugin;

class AttributePlugin{

    /**
     * Perform actions after object save
     *
     * @param AbstractModel $object
     * @param string $attribute
     * @return $this
     * @throws \Exception
     */
    public function beforeSaveAttribute(AbstractModel $object, $attribute)
    {
        if ($attribute instanceof AbstractAttribute) {
            $attributes = $attribute->getAttributeCode();
        } elseif (is_string($attribute)) {
            $attributes = [$attribute];
        } else {
            $attributes = $attribute;
        }
        if (is_array($attributes) && !empty($attributes)) {
            $this->getConnection()->beginTransaction();
            $data = array_intersect_key($object->getData(), array_flip($attributes));
            try {
                $this->_beforeSaveAttribute($object, $attributes);
                if ($object->getId() && !empty($data)) {
                    $this->getConnection()->update(
                        $object->getResource()->getMainTable(),
                        $data,
                        [$object->getResource()->getIdFieldName() . '= ?' => (int)$object->getId()]
                    );
                    $object->addData($data);
                }
                $this->_afterSaveAttribute($object, $attributes);
                $this->getConnection()->commit();
            } catch (\Exception $e) {
                $this->getConnection()->rollBack();
                throw $e;
            }
        }
        return $this;
    }
}