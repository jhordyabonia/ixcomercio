<?php
declare(strict_types=1);

namespace Intcomex\RewriteBackendReindex\Controller\Adminhtml\Indexer;

use Exception;
use Intcomex\RewriteBackendReindex\Controller\Adminhtml\Indexer as ControllerIndexer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\StateInterface;

class MassReset extends ControllerIndexer
{
    /**
     * Display processes grid action.
     *
     * @return void
     */
    public function execute()
    {
        $indexerIds = $this->getRequest()->getParam('indexer_ids');
        if (!is_array($indexerIds)) {
            $this->messageManager->addErrorMessage(__('Please select indexers.'));
        } else {
            try {
                foreach ($indexerIds as $indexerId) {
                    $indexer = $this->indexerRegistry->get($indexerId);
                    $indexer->getState()
                        ->setStatus(StateInterface::STATUS_INVALID)
                        ->save();
                    $indexer->reindexAll();
                }
                $this->messageManager->addSuccessMessage(__('Total of %1 index(es) have reset data.', count($indexerIds)));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Cannot initialize the reset process.'));
            }
        }
        $this->_redirect('*/*/list');
    }
}
