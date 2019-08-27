<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @documentation: https://developer.zendesk.com/rest_api/docs/core/users
 */
namespace Wagento\Zendesk\Helper\Api;

class User extends AbstractApi
{

    /**
     * List Users:
     * GET /api/v2/users.json
     * GET /api/v2/groups/{id}/users.json
     * GET /api/v2/organizations/{id}/users.json
     */
    const LIST_USERS = '/api/v2/users.json';

    // Show User: GET /api/v2/users/{id}.json
    const SHOW_USER = '/api/v2/users/%s.json';

    // Create User: POST /api/v2/users.json
    const CREATE_USER = '/api/v2/users.json';

    // Find User: GET /api/v2/users/search.json?query={query}
    const GET_USER = '/api/v2/users/search.json';

    // Search Users: GET /api/v2/users/search.json?query={query}
    const SEARCH_USERS = '/api/v2/users/search.json';

    // Show the currently authenticated user: GET /api/v2/users/me.json
    const GET_USER_ME = '/api/v2/users/me.json';

    // Add a new identity to a user profile POST /api/v2/users/{user_id}/identities.json
    const ADD_NEW_IDENTITY = '/api/v2/users/%s/identities.json';

    // Update User PUT /api/v2/users/{id}.json
    const UPDATE_USER = '/api/v2/users/%s.json';

    /**
     * Show User Information.
     *
     * @param $userId
     * @return array
     */
    public function showUser($userId)
    {
        $endPoint = sprintf(self::SHOW_USER, $userId);
        $response = $this->get($endPoint);
        $data = json_decode($response, true);
        return isset($data['user']) ? $data['user'] : null;
    }

    /**
     * List Users.
     *
     * Returns a maximum of 100 users per page.
     *
     * @return array
     */
    public function listUsers()
    {
        $response = $this->get(self::LIST_USERS);
        $data = json_decode($response, true);
        return isset($data['users']) ? $data['users'] : [];
    }

    /**
     * Create user.
     *
     * @param array $data
     * @return array|null
     */
    public function createUser($data)
    {
        $response = $this->post(self::CREATE_USER, json_encode(['user' => $data]));
        $data = json_decode($response, true);

        return isset($data['user']['id']) ? $data['user']['id'] : null;
    }

    /**
     * Find the Email.
     *
     * @param string $email
     * @return boolean | array
     */
    public function getUser($email)
    {
        $response = $this->get(self::GET_USER, ['query' => $email, 'per_page' => 30]);
        $data = json_decode($response, true);

        return isset($data["users"]) && count($data["users"]) > 0 ? array_shift($data['users']) : [];
    }

    /**
     * @param $email
     * @return array | null
     */
    public function searchUsers($email)
    {
        $response = $this->get(self::SEARCH_USERS, ['query' => $email]);
        $data = json_decode($response, true);
        return isset($data['users']) ? $data['users'] : null;
    }

    /**
     * Show the currently authenticated server.
     *
     * @return array | false
     */
    public function getMeUser()
    {
        $response = $this->get(self::GET_USER_ME);
        $data = json_decode($response, true);
        return isset($data['user']) ? $data['user'] : false;
    }

    /**
     * Add Identity.
     *
     * @param $user_id
     * @param $data
     * @return array
     */
    public function addIdentity($user_id, $data)
    {
        $responde = $this->post(sprintf(self::ADD_NEW_IDENTITY, $user_id), $data);
        $data = json_decode($responde, true);

        return isset($data['identity']) ? $data['identity'] : [];
    }

    /**
     * Update User Information.
     *
     * @param $user_id
     * @param $data
     * @return array
     */
    public function updateUser($user_id, $data)
    {
        $endpoint = sprintf(self::UPDATE_USER, $user_id);
        $response = $this->put($endpoint, json_encode(['user' => $data]));
        $data = json_decode($response, true);

        return isset($data['user']['id']) ? $data['user'] : null;
    }
}
