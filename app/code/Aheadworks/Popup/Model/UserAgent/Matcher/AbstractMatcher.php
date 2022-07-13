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
namespace Aheadworks\Popup\Model\UserAgent\Matcher;

/**
 * Class AbstractMatcher
 * @package Aheadworks\Popup\Model\UserAgent\Matcher
 */
abstract class AbstractMatcher
{
    /**
     * Check if user agent match to signatures
     *
     * @param string $userAgent
     * @param array $serverParams
     * @return mixed
     */
    abstract protected function match($userAgent, $serverParams);

    /**
     * Match agent with list of signatures
     *
     * @param  string $userAgent
     * @param  array $signs
     * @return bool
     */
    protected function matchWithSignatures($userAgent, $signs)
    {
        $userAgent = strtolower($userAgent);
        foreach ($signs as $signature) {
            if (!empty($signature)) {
                if (strpos($userAgent, $signature) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
