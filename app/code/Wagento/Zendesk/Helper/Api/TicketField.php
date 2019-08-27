<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @documentation: https://developer.zendesk.com/rest_api/docs/core/ticket_fields
 */
namespace Wagento\Zendesk\Helper\Api;

class TicketField extends AbstractApi
{

    // Create Ticket Field: POST /api/v2/ticket_fields.json
    const CREATE_TICKET_FIELD = '/api/v2/ticket_fields.json';

    // List Ticket Fields: GET /api/v2/ticket_fields.json
    const LIST_TICKET_FIELDS = '/api/v2/ticket_fields.json';

    // Show Ticket Field: GET /api/v2/ticket_fields/{id}.json
    const SHOW_TICKET_FIELD = '/api/v2/ticket_fields/%s.json';

    /**
     * Returns a list of all ticket fields in your account.
     * Fields are returned in the order that you specify in your Ticket Fields configuration in Zendesk Support.
     * Clients should cache this resource for the duration of their API usage and map the id for each ticket
     * field to the values returned under the fields attributes on the Ticket resource.
     *
     * @return array
     */
    public function getList()
    {
        $response = $this->get(self::LIST_TICKET_FIELDS);
        $data = json_decode($response, true);
        return isset($data['ticket_fields']) ? $data['ticket_fields'] : [];
    }

    /**
     * Creates any of the following custom field types:
     * text, textarea, checkbox, date, integer, decimal, regexp, tagger (custom dropdown).
     *
     * @param $data
     * @return null
     */
    public function createTicketField($data)
    {
        $response = $this->post(self::CREATE_TICKET_FIELD, json_encode(['ticket_field' => $data]));
        $data = json_decode($response, true);
        return isset($data['ticket_field']['id']) ? $data['ticket_field']['id'] : null;
    }

    /**
     * Show Ticket Field.
     *
     * @param $id
     * @return array
     */
    public function showTicketField($id)
    {
        $endpoint = sprintf(self::SHOW_TICKET_FIELD, $id);
        $response = $this->get($endpoint);
        $data = json_decode($response, true);
        return isset($data['ticket_field']) ? $data['ticket_field'] : [];
    }
}
