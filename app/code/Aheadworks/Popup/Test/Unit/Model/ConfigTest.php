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
namespace Aheadworks\Popup\Test\Unit\Model;

use Aheadworks\Popup\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigTest
 * @package Aheadworks\Popup\Test\Unit\Model
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $data = [
            'scopeConfig' => $this->scopeConfigMock,
        ];
        $this->object = $objectManager->getObject(Config::class, $data);
    }

    /**
     * Test getHidePopupForSearchEngines method
     *
     * @param bool $result
     * @dataProvider boolDataProvider
     */
    public function testGetHidePopupForSearchEngines($result)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_HIDE_POPUP_FOR_SEARCH_ENGINES, ScopeInterface::SCOPE_STORE)
            ->willReturn($result);

        $this->assertEquals($result, $this->object->getHidePopupForSearchEngines());
    }

    /**
     * Test getHidePopupForMobileDevices method
     *
     * @param bool $result
     * @dataProvider boolDataProvider
     */
    public function testGetHidePopupForMobileDevices($result)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_HIDE_POPUP_FOR_MOBILE_DEVICES, ScopeInterface::SCOPE_STORE)
            ->willReturn($result);

        $this->assertEquals($result, $this->object->getHidePopupForMobileDevices());
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
