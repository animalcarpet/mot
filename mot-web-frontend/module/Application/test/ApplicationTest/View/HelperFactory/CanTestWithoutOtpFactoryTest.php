<?php

namespace ApplicationTest\View\HelperFactory;

use Application\View\Helper\CanTestWithoutOtp;
use Application\View\HelperFactory\CanTestWithoutOtpFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Zend\ServiceManager\ServiceManager;
use Application\Service\CanTestWithoutOtpService;
use Zend\View\HelperPluginManager;
use DvsaCommonTest\TestUtils\XMock;

class CanTestWithoutOtpFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $canTestWithoutOtpServiceMock = XMock::of(CanTestWithoutOtpService::class);

        $serviceManager = new ServiceManager();
        $serviceManager->setService(CanTestWithoutOtpService::class, $canTestWithoutOtpServiceMock);

        $viewHelperServiceLocator = XMock::of(HelperPluginManager::class);
        $viewHelperServiceLocator->expects($this->any())->method('getServiceLocator')->willReturn($serviceManager);

        $twoFaFeatureToggle = XMock::of(TwoFaFeatureToggle::class);
        $serviceManager->setService(TwoFaFeatureToggle::class, $twoFaFeatureToggle);

        $factory = new CanTestWithoutOtpFactory();
        $helper = $factory->createService($viewHelperServiceLocator);
        $this->assertInstanceOf(CanTestWithoutOtp::class, $helper);
    }
}