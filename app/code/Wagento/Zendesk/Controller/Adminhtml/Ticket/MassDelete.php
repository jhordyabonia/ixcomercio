<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Controller\Adminhtml\Ticket;

class MassDelete extends \Magento\Backend\App\Action
{
    protected $_filter;

    /**
     * Helper Ticket
     *
     * @var \Wagento\Zendesk\Helper\Api\Ticket
     */
    protected $_ticket;

    /**
     * constructor
     *
     * @param \Wagento\Zendesk\Helper\Api\Ticket $ticket
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Wagento\Zendesk\Helper\Api\Ticket $ticket,
        \Magento\Backend\App\Action\Context $context
    ) {
    
        $this->_ticket = $ticket;
        parent::__construct($context);
    }

    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($selected_ticket_ids = $this->getRequest()->getParam('selected')) {
            $array_chunk_ids = array_chunk($selected_ticket_ids, 99);
            foreach ($array_chunk_ids as $array_ids) {
                $ticket_ids = implode(",", $array_ids);
                $this->_ticket->bulkDeleteTickets($ticket_ids);
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', count($selected_ticket_ids)));
        }
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
