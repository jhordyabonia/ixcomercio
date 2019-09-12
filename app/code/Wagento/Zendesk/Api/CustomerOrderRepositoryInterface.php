<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Api;

/**
 * CustomerOrder repository interface.
 *
 * Gets customer's order info just read is allowed.
 *
 * @api
 */
interface CustomerOrderRepositoryInterface
{

    /**
     * Loads a specified customer order information.
     *
     * @param string $email
     * @return \Wagento\Zendesk\Api\Data\CustomerOrderInterface Customer Order Interface.
     */
    public function get($email);
}
