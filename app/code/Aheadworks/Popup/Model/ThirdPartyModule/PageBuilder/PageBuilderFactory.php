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
namespace Aheadworks\Popup\Model\ThirdPartyModule\PageBuilder;

use Magento\Framework\Exception\LocalizedException;
use Magento\PageBuilder\Component\Form\Element\Wysiwyg;
use Magento\Framework\ObjectManagerInterface;
use Aheadworks\Popup\Model\ThirdPartyModule\Manager as ModuleManager;

/**
 * Class PageBuilderFactory
 * @package Aheadworks\Popup\Model\ThirdPartyModule\PageBuilder
 */
class PageBuilderFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ModuleManager $moduleManager
    ) {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Create Page Builder instance
     *
     * @param array $arguments
     * @return Wysiwyg
     * @throws LocalizedException
     */
    public function create($arguments)
    {
        if ($this->moduleManager->isMagePageBuilderModuleEnabled()) {
            return $this->objectManager->create(Wysiwyg::class, $arguments);
        }

        throw new LocalizedException(__('Page Builder is not available'));
    }
}
