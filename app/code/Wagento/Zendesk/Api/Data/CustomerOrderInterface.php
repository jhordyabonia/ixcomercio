<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Api\Data;

interface CustomerOrderInterface
{

    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const EMAIL = 'email';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const CREATED_AT = 'created_at';
    const GROUP = 'group';
    const LIFETIME_SALES = 'lifetime_sales';
    const ORDERS = 'orders';

    /**
     * Get customer email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email address.
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get customer firstname.
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Set first name.
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);

    /**
     * Get customer lastname.
     *
     * @return string
     */
    public function getLastname();

    /**
     * Set last name.
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Get created at time.
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at time.
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get customer group.
     *
     * @return string
     */
    public function getGroup();

    /**
     * Set group.
     *
     * @param string $group
     * @return $this
     */
    public function setGroup($group);

    /**
     * Get lifetime sales for the customer.
     *
     * @return string|null
     */
    public function getLifetimeSales();

    /**
     * Set lifetime sales for the customer.
     *
     * @param string $lifetimeSales
     * @return $this
     */
    public function setLifetimeSales($lifetimeSales);

    /**
     * Get orders for the customer.
     *
     * @return \Wagento\Zendesk\Api\Data\OrderReaderInterface[] Array of orders.
     */
    public function getOrders();

    /**
     * Sets orders for the customer.
     *
     * @param \Wagento\Zendesk\Api\Data\OrderReaderInterface[] $orders
     * @return $this
     */
    public function setOrder($orders);
}
