<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @documentation: https://developer.zendesk.com/rest_api/docs/core/user_fields
 */
namespace Wagento\Zendesk\Helper\Api;

class UserField extends AbstractApi
{
    // Create User Fields: POST /api/v2/user_fields.json
    const CREATE_USER_FIELDS = '/api/v2/user_fields.json';

    // List User Fields: GET /api/v2/user_fields.json
    const LIST_USER_FIELDS = '/api/v2/user_fields.json';

    /**
     *
     * Types of custom fields that can be created are:
     * text (default when no "type" is specified)
     * textarea
     * checkbox
     * date
     * integer
     * decimal
     * regexp
     * tagger (custom dropdown)
     *
     * @param $field
     * @return array
     */
    public function createUserFields($field)
    {
        $response = $this->post(self::CREATE_USER_FIELDS, json_encode(['user_field' => $field]));
        $data = json_decode($response, true);

        return isset($data['user_field']['id']) ? $data['user_field']['id'] : [];
    }

    /**
     * Returns a list of all custom User Fields in your account.
     * Fields are returned in the order that you specify in your User Fields configuration in Zendesk Support.
     * Clients should cache this resource for the duration of their API usage and map the key for each User Field to the values returned under the user_fields attribute on the User resource.
     *
     * @return array
     */
    public function listUserFields()
    {
        $response = $this->get(self::LIST_USER_FIELDS);
        $data = json_decode($response, true);
        return isset($data['user_fields']) ? $data['user_fields'] : [];
    }
}
