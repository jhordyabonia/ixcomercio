<?php
declare(strict_types=1);

namespace Intcomex\RewriteBackendReindex\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Indexer\IndexerRegistry;

abstract class Indexer extends Action
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * Reindex constructor.
     *
     * @param Context $context
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        Context $context,
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
        parent::__construct($context);
    }
}
