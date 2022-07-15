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
namespace Aheadworks\Popup\Model\ResourceModel\EntityRelation;

use Magento\Framework\Model\AbstractModel;

/**
 * Interface HandlerInterface
 * @package Aheadworks\Popup\Model\ResourceModel\EntityRelation
 */
interface HandlerInterface
{
    /**
     * After save operation
     *
     * @param AbstractModel $entity
     * @return void
     */
    public function afterSave($entity);

    /**
     * After load operation
     *
     * @param AbstractModel $entity
     * @return void
     */
    public function afterLoad($entity);
}
