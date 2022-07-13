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
 * Class Mobile
 * @package Aheadworks\Popup\Model\UserAgent\Matcher
 */
class Mobile extends AbstractMatcher
{
    /**
     * User Agent Signatures
     *
     * @var array
     */
    private $agentSignature = [
        'iphone',
        'ipod',
        'ipad',
        'android',
        'blackberry',
        'opera mini',
        'opera mobi',
        'palm',
        'palmos',
        'elaine',
        'windows ce',
        'icab',
        '_mms',
        'ahong',
        'archos',
        'armv',
        'astel',
        'avantgo',
        'benq',
        'blazer',
        'brew',
        'com2',
        'compal',
        'danger',
        'pocket',
        'docomo',
        'epoc',
        'ericsson',
        'eudoraweb',
        'hiptop',
        'htc-',
        'htc_',
        'iemobile',
        'iris',
        'j-phone',
        'kddi',
        'kindle',
        'lg ',
        'lg-',
        'lg/',
        'lg;lx',
        'lge vx',
        'lge',
        'lge-',
        'lge-cx',
        'lge-lx',
        'lge-mx',
        'linux armv',
        'maemo',
        'midp',
        'mini 9.5',
        'minimo',
        'mob-x',
        'mobi',
        'mobile',
        'mobilephone',
        'mot 24',
        'mot-',
        'motorola',
        'n410',
        'netfront',
        'nintendo wii',
        'nintendo',
        'nitro',
        'nokia',
        'novarra-vision',
        'nuvifone',
        'openweb',
        'opwv',
        'palmsource',
        'pdxgw',
        'phone',
        'playstation',
        'polaris',
        'portalmmm',
        'qt embedded',
        'reqwirelessweb',
        'sagem',
        'sam-r',
        'samsu',
        'samsung',
        'sec-',
        'sec-sgh',
        'semc-browser',
        'series60',
        'series70',
        'series80',
        'series90',
        'sharp',
        'sie-m',
        'sie-s',
        'smartphone',
        'sony cmd',
        'sonyericsson',
        'sprint',
        'spv',
        'symbian os',
        'symbian',
        'symbianos',
        'telco',
        'teleca',
        'treo',
        'up.browser',
        'up.link',
        'vodafone',
        'vodaphone',
        'webos',
        'wml',
        'windows phone os 7',
        'wireless',
        'wm5 pie',
        'wms pie',
        'xiino',
        'wap',
        'up/',
        'psion',
        'j2me',
        'klondike',
        'kbrowser'
    ];

    /**
     * Http accept terms
     *
     * @var array
     */
    private $acceptTerms = [
        'midp',
        'wml',
        'vnd.rim',
        'vnd.wap',
        'j2me',
    ];

    /**
     * first letters of mobile agents
     *
     * @var array
     */
    private $agentBegin = [
        'w3c ',
        'acs-',
        'alav',
        'alca',
        'amoi',
        'audi',
        'avan',
        'benq',
        'bird',
        'blac',
        'blaz',
        'brew',
        'cell',
        'cldc',
        'cmd-',
        'dang',
        'doco',
        'eric',
        'hipt',
        'inno',
        'ipaq',
        'java',
        'jigs',
        'kddi',
        'keji',
        'leno',
        'lg-c',
        'lg-d',
        'lg-g',
        'lge-',
        'maui',
        'maxo',
        'midp',
        'mits',
        'mmef',
        'mobi',
        'mot-',
        'moto',
        'mwbp',
        'nec-',
        'newt',
        'noki',
        'palm',
        'pana',
        'pant',
        'phil',
        'play',
        'port',
        'prox',
        'qwap',
        'sage',
        'sams',
        'sany',
        'sch-',
        'sec-',
        'send',
        'seri',
        'sgh-',
        'shar',
        'sie-',
        'siem',
        'smal',
        'smar',
        'sony',
        'sph-',
        'symb',
        't-mo',
        'teli',
        'tim-',
        'tosh',
        'tsm-',
        'upg1',
        'upsi',
        'vk-v',
        'voda',
        'wap-',
        'wapa',
        'wapi',
        'wapp',
        'wapr',
        'webc',
        'winw',
        'winw',
        'xda',
        'xda-',
    ];

    /**
     * {@inheritDoc}
     */
    public function match($userAgent, $server)
    {
        if (isset($server['all_http'])) {
            if (strpos(strtolower(str_replace(' ', '', $server['all_http'])), 'operam') !== false) {
                return true;
            }
        }

        if (isset($server['http_x_wap_profile']) || isset($server['http_profile'])) {
            return true;
        }

        if (isset($server['http_accept'])) {
            if ($this->matchWithSignatures($server['http_accept'], $this->acceptTerms)) {
                return true;
            }
        }

        if ($this->userAgentStart($userAgent)) {
            return true;
        }

        if ($this->matchWithSignatures($userAgent, $this->agentSignature)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve beginning clause of user agent
     *
     * @param  string $userAgent
     * @return string
     */
    private function userAgentStart($userAgent)
    {
        $mobileAgent = strtolower(substr($userAgent, 0, 4));

        return (in_array($mobileAgent, $this->agentBegin));
    }
}
