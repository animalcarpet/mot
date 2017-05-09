<?php

namespace ApplicationTest\View\HelperFactory;

use Application\View\Helper\DashboardDataProvider;
use Application\View\HelperFactory\DashboardDataProviderFactory;
use Dashboard\Data\ApiDashboardResource;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\Auth\GrantAllAuthorisationServiceStub;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;

class DashboardDataProviderFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $identityProviderMock = XMock::of(MotIdentityProviderInterface::class);
        $apiServiceMock = XMock::of(ApiDashboardResource::class);
        $authServiceMock = new GrantAllAuthorisationServiceStub();

        $serviceManager = new ServiceManager();
        $serviceManager->setService('MotIdentityProvider', $identityProviderMock);
        $serviceManager->setService(ApiDashboardResource::class, $apiServiceMock);
        $serviceManager->setService('AuthorisationService', $authServiceMock);

        $viewHelperServiceLocator = XMock::of(HelperPluginManager::class);
        $viewHelperServiceLocator->expects($this->any())->method('getServiceLocator')->willReturn($serviceManager);

        $factory = new DashboardDataProviderFactory();
        $service = $factory->createService($viewHelperServiceLocator);

        $this->assertInstanceOf(DashboardDataProvider::class, $service);
    }
}
