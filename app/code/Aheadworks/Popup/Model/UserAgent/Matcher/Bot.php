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
 * Class Bot
 * @package Aheadworks\Popup\Model\UserAgent\Matcher
 */
class Bot extends AbstractMatcher
{
    /**
     * Bot User Agent Signatures
     *
     * @var array
     */
    private $botSignatures = [
        'googlebot',
        'msnbot',
        'slurp',
        'yahoo',
        'alexa',
        'appie',
        'archiver',
        'ask jeeves',
        'baiduspider',
        'bot',
        'crawl',
        'crawler',
        'curl',
        'eventbox',
        'facebookexternal',
        'fast',
        'feedfetcher-google',
        'firefly',
        'froogle',
        'gigabot',
        'girafabot',
        'google',
        'htdig',
        'infoseek',
        'inktomi',
        'java',
        'larbin',
        'looksmart',
        'mechanize',
        'mediapartners-google',
        'monitor',
        'nambu',
        'nationaldirectory',
        'novarra',
        'pear',
        'perl',
        'python',
        'rabaz',
        'radian',
        'rankivabot',
        'scooter',
        'sogou web spider',
        'spade',
        'sphere',
        'spider',
        'technoratisnoop',
        'tecnoseek',
        'teoma',
        'toolbar',
        'transcoder',
        'twitt',
        'url_spider_sql',
        'webalta crawler',
        'webbug',
        'webfindbot',
        'wordpress',
        'www.galaxy.com',
        'yahoo! searchmonkey',
        'yahoo! slurp',
        'yandex',
        'zyborg',
    ];

    /**
     * {@inheritDoc}
     */
    public function match($userAgent, $server)
    {
        return $this->matchWithSignatures($userAgent, $this->botSignatures);
    }
}
