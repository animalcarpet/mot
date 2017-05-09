<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Listener\Factory;

use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\GotoUrlServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlValidatorService;
use Zend\ServiceManager\ServiceManager;

class GotoUrlServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        $urlValidatorMock = $this->getMockBuilder(GotoUrlValidatorService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sm = new ServiceManager();
        $sm->setAllowOverride(true);
        $sm->setService(GotoUrlValidatorService::class, $urlValidatorMock);

        $urlValidatorFactory = new GotoUrlServiceFactory();
        $urlValidator = $urlValidatorFactory->createService($sm);

        $this->assertInstanceOf(GotoUrlService::class, $urlValidator);
    }
}
