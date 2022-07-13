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

namespace Aheadworks\Popup\Model\Event\Plugin;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ContextPlugin
 * @package Aheadworks\Popup\Model\Event\Plugin
 */
class ContextPlugin
{
    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @param HttpRequest $request
     */
    public function __construct(
        HttpRequest $request
    ) {
        $this->request = $request;
    }

    /**
     * Set render type for correct work of Recently Viewed, Recently Compared Widgets
     *
     * @param ContextInterface $context
     * @param string $originalType
     * @return string
     */
    public function afterGetAcceptType(
        ContextInterface $context,
        $originalType
    ) {
        if ($this->request->getParam('aw_popup')) {
            return 'html';
        }

        return $originalType;
    }
}
