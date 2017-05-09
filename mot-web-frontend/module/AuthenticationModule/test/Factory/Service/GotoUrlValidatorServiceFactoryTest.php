<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Listener\Factory;

use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\GotoUrlValidatorServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlValidatorService;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use Zend\ServiceManager\ServiceManager;

class GotoUrlValidatorServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        $openAmClientOptionsMock = $this->getMockBuilder(OpenAMClientOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCookieDomain'])
            ->getMock();

        $openAmClientOptionsMock->expects($this->once())->method('getCookieDomain')->willReturn('.mot.gov.uk');

        $sm = new ServiceManager();
        $sm->setAllowOverride(true);
        $sm->setService(OpenAMClientOptions::class, $openAmClientOptionsMock);

        $urlValidatorFactory = new GotoUrlValidatorServiceFactory();
        $urlValidator = $urlValidatorFactory->createService($sm);

        $this->assertInstanceOf(GotoUrlValidatorService::class, $urlValidator);
    }
}
