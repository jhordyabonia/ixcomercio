<?php
/**
 * Aheadworks Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://ecommerce.aheadworks.com/end-user-license-agreement/
 *
 * @package    Popup
 * @version    1.2.3
 * @copyright  Copyright (c) 2020 Aheadworks Inc. (http://www.aheadworks.com)
 * @license    https://ecommerce.aheadworks.com/end-user-license-agreement/
 */
namespace Aheadworks\Popup\Model\ResourceModel\EntityRelation\Handler;

use Aheadworks\Popup\Model\ResourceModel\EntityRelation\HandlerInterface;

/**
 * Class Composite
 * @package Aheadworks\Popup\Model\ResourceModel\EntityRelation\Handler
 */
class Composite implements HandlerInterface
{
    /**
     * @var HandlerInterface[]
     */
    private $handlers;

    /**
     * @param array $handlers
     */
    public function __construct(
        array $handlers = []
    ) {
        $this->handlers = $handlers;
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave($entity)
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof HandlerInterface) {
                $handler->afterSave($entity);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterLoad($entity)
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof HandlerInterface) {
                $handler->afterLoad($entity);
            }
        }
    }
}
