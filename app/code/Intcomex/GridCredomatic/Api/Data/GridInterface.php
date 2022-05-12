<?php

namespace Intcomex\GridCredomatic\Api\Data;

interface GridInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const ORDER_ID = 'order_id';
    const RESPONSE = 'response';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const TOKEN = 'token';

    public function getId();

    public function getOrderId();

    public function getResponse();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getToken();

   
}
