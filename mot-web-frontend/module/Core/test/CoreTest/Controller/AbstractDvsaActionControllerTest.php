<?php
/**
 * This file is part of the DVSA MOT Frontend package.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace CoreTest\Controller;

use Core\Controller\AbstractDvsaActionController;
use DvsaFeature\FeatureToggles;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractDvsaActionControllerTest extends \PHPUnit_Framework_TestCase
{
    const ENABLED_FEATURE = 'enabledFeature';
    const DISABLED_FEATURE = 'disabledFeature';

    private $controller;

    public function setUp()
    {
        $featureToggles = $this
            ->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled'])
            ->getMock();
        $featureToggles
            ->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValueMap([
                [self::ENABLED_FEATURE, true],
                [self::DISABLED_FEATURE, false],
            ]));

        $serviceLocator = $this->getMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('Feature\FeatureToggles')
            ->willReturn($featureToggles);

        $this->controller = $this
            ->getMockBuilder(AbstractDvsaActionController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getServiceLocator'])
            ->getMock();
        $this
            ->controller
            ->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($serviceLocator);
    }

    public function testIsFeatureEnabled()
    {
        $this->assertTrue($this->controller->isFeatureEnabled(self::ENABLED_FEATURE));
        $this->assertFalse($this->controller->isFeatureEnabled(self::DISABLED_FEATURE));
    }

    /**
     * @expectedException \DvsaFeature\Exception\FeatureNotAvailableException
     */
    public function testAssertDisabledFeatureThrowsException()
    {
        $this->controller->assertFeatureEnabled(self::DISABLED_FEATURE);
    }

    public function testAssertEnabledFeatureDoesNotThrowException()
    {
        $this->controller->assertFeatureEnabled(self::ENABLED_FEATURE);
    }
}
