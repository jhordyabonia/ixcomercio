<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Observer;

use Bss\SeoReport\Observer\ExpiredEntity;
use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ExpiredCate implements ObserverInterface
{
    /**
     * @var ExpiredEntity
     */
    protected $expiredEntity;

    /**
     * ExpiredCate constructor.
     * @param ExpiredEntity $expiredEntity
     */
    public function __construct(
        ExpiredEntity $expiredEntity
    ) {
        $this->expiredEntity = $expiredEntity;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getCategory();
        $this->expiredEntity->setExpiredEntity($category->getId(), 'category');
        return $this;
    }
}
