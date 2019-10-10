<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Ui\Component;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Wagento\Zendesk\Helper\Api\Ticket
     */
    private $ticket;

    /**
     * @var \Wagento\Zendesk\Helper\Api\User
     */
    private $user;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    private $backedUrl;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param \Wagento\Zendesk\Helper\Api\Ticket $ticket
     * @param \Magento\Backend\Helper\Data $backedUrl
     * @param array $meta
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        \Wagento\Zendesk\Helper\Api\Ticket $ticket,
        \Wagento\Zendesk\Helper\Api\User $user,
        \Magento\Backend\Helper\Data $backedUrl,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {
    
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->ticket = $ticket;
        $this->user = $user;
        $this->urlBuilder = $urlBuilder;
        $this->backedUrl = $backedUrl;
    }

    public function getData()
    {
        $users = $this->user->listUsers();
        $emails = array_column($users, 'email', 'id');

        //sorting
        if ($sorting = $this->request->getParam('sorting')) {
            $tickets = $this->getSorting($this->ticket->listTickets(), $sorting, $emails);
        } else {
            $tickets = $this->ticket->listTickets();
        }

        $data = [];
        $result = [];
        $data['totalRecords'] = count($tickets);

        //paging
        $searchCriteria = $this->searchCriteriaBuilder->getData();

        $page_size = $searchCriteria['page_size'];
        $current_page = $searchCriteria['current_page'];

        $first = (($page_size * $current_page) - $page_size);

        if (($first + $page_size) < count($tickets)) {
            $last = $page_size * $current_page;
        } else {
            $last = count($tickets);
        }
        //end

        for ($i = $first; $i < $last; $i++) {
            $ticket = $tickets[$i];

            $result[] = $ticket;
        }

        $data['items'] = $result;

        return $data;
    }

    /**
     * getSorting Tickets.
     * @param array $tickets
     * @param array $sorting
     * @param array $emails
     */
    private function getSorting($tickets, $sorting, $emails)
    {
        $array_sorting = [];
        $key = 0;
        foreach ($tickets as $ticket) {
            $ticket['email'] = (isset($emails[$ticket['requester_id']]) ? $emails[$ticket['requester_id']] : '');
            $array_sorting[$ticket[$sorting['field']] . $key++] = $ticket;
        }

        if ($sorting['direction'] == 'asc') {
            ksort($array_sorting);
        } else {
            krsort($array_sorting);
        }

        return array_values($array_sorting);
    }
}
