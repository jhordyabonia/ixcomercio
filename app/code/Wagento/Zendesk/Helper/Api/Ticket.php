<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @documentation: https://developer.zendesk.com/rest_api/docs/core/tickets
 */
namespace Wagento\Zendesk\Helper\Api;

class Ticket extends AbstractApi
{
    // List Tickets: GET /api/v2/tickets.json
    const LIST_TICKETS = '/api/v2/tickets.json';

    // List User Tickets: /api/v2/users/{user_id}/tickets/requested.json
    const LIST_USER_TICKETS = '/api/v2/users/%s/tickets/requested.json';

    // List Recent Tickets for user id: /api/v2/tickets/recent.json
    const LIST_RECENT_TICKETS = '/api/v2/tickets/recent.json';

    // Show Ticket: GET /api/v2/tickets/{id}.json
    const SHOW_TICKET = '/api/v2/tickets/%s.json';

    // Create Ticket: POST /api/v2/tickets.json
    const CREATE_TICKET = '/api/v2/tickets.json';

    // Update Ticket: PUT /api/v2/tickets/{id}.json
    const UPDATE_TICKET = '/api/v2/tickets/%s.json';

    // Delete Ticket: DELETE /api/v2/tickets/{id}.json
    const DELETE_TICKET = '/api/v2/tickets/%s.json';

    // Bulk Delete Tickets: DELETE /api/v2/tickets/destroy_many.json?ids={ids}
    const BULK_DELETE_TICKETS = '/api/v2/tickets/destroy_many.json?ids=%s';

    /**
     * Returns a maximum of 100 tickets per page.
     *
     * Tickets are ordered chronologically by created date, from oldest to newest.
     * The first ticket listed may not be the absolute oldest ticket in your account due to ticket archiving.
     * To get a list of all tickets in your account, use the Incremental Ticket Export endpoint.
     * For more filter options, use the Search API.
     * You can also sideload related records with the tickets. See Side-Loading.
     * @return array
     */
    public function listTickets()
    {
        $response = $this->get(self::LIST_TICKETS);
        $data = json_decode($response, true);
        return isset($data['tickets']) ? $data['tickets'] : [];
    }

    /**
     * List User Tickets.
     *
     * @param $id
     * @return array
     */
    public function listUserTickets($userId)
    {
        $endpoint = sprintf(self::LIST_USER_TICKETS, $userId);
        $response = $this->get($endpoint);
        $data = json_decode($response, true);
        return isset($data['tickets']) ? $data['tickets'] : [];
    }

    /**
     * Response if the ticket has been created successfully or not
     *
     * @param array $data
     * @return int | null
     */
    public function create($data)
    {
        $response = $this->post(self::CREATE_TICKET, json_encode(['ticket' => $data]));
        $data = json_decode($response, true);

        // validate OK response
        return isset($data['ticket']["id"]) && $data['ticket']["id"] ? $data['ticket']["id"] : null;
    }

    /**
     * Update Ticket
     *
     * @param $id
     * @param $data
     * @return null
     */
    public function update($id, $data)
    {
        $endpoint = sprintf(self::UPDATE_TICKET, $id);
        $response = $this->put($endpoint, json_encode(['ticket' => $data]));
        $data = json_decode($response, true);

        // validate OK response
        return isset($data['ticket']["id"]) && $data['ticket']["id"] ? $data['ticket']["id"] : null;
    }

    /**
     * Delete Ticket
     *
     * @param $id
     */
    public function deleteTicket($id)
    {
        $response = $this->delete(sprintf(self::DELETE_TICKET, $id));

        return $response;
    }

    /**
     * Accepts a comma-separated list of up to 100 ticket ids.
     *
     * @param $ids
     */
    public function bulkDeleteTickets($ids)
    {
        $response = $this->delete(sprintf(self::BULK_DELETE_TICKETS, $ids));
        return $response;
    }

    /**
     * Returns a number of ticket properties, but not the ticket comments. To get the comments, use List Comments.
     *
     * @param $id
     * @return array | bool
     */
    public function showTicket($id)
    {
        $endpoint = sprintf(self::SHOW_TICKET, $id);
        $response = $this->get($endpoint);
        $data = json_decode($response, true);
        return isset($data['ticket']) ? $data['ticket'] : [];
    }
}
