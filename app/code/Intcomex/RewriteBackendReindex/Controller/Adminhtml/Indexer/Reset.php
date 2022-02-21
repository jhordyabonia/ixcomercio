<?php
declare(strict_types=1);

namespace Intcomex\RewriteBackendReindex\Controller\Adminhtml\Indexer;

use Exception;
use Intcomex\RewriteBackendReindex\Controller\Adminhtml\Indexer as ControllerIndexer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\StateInterface;

class Reset extends ControllerIndexer
{
    /**
     * Display processes grid action.
     *
     * @return void
     */
    public function execute()
    {
        $indexerId = $this->getRequest()->getParam('id');
        $indexer = $this->indexerRegistry->get($indexerId);
        if ($indexer && $indexer->getId()) {
            try {
                $indexer->getState()
                    ->setStatus(StateInterface::STATUS_INVALID)
                    ->save();
                $indexer->reindexAll();
                $this->messageManager->addSuccessMessage(__('%1 index was reset.', $indexer->getTitle()));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('There was a problem with reseting process.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Cannot initialize the reset process.'));
        }
        $this->_redirect('*/*/list');
    }
}
