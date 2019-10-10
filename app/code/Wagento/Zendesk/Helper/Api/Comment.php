<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Helper\Api;

class Comment extends AbstractApi
{
    // List Comments: GET /api/v2/tickets/{ticket_id}/comments.json
    const LIST_COMMENTS = '/api/v2/tickets/%s/comments.json';

    public function getTicketComments($ticketId)
    {
        $endpoint = sprintf(self::LIST_COMMENTS, $ticketId);
        $res = $this->get($endpoint);
        $data = json_decode($res, true);
        return isset($data['comments']) ? $data['comments'] : [];
    }
}
