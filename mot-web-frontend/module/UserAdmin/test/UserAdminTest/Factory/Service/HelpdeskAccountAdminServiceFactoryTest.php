<?php

namespace UserAdminTest\Factory\Service;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaClient\Mapper\UserAdminMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Factory\Service\HelpdeskAccountAdminServiceFactory;
use UserAdmin\Service\HelpdeskAccountAdminService;
use Zend\ServiceManager\ServiceManager;

/**
 * Test for {@link \UserAdmin\Factory\Service\HelpdeskAccountAdminServiceFactory}.
 */
class HelpdeskAccountAdminServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        // given
        $sut = new HelpdeskAccountAdminServiceFactory();

        $serviceManager = new ServiceManager();

        $userAdminMapperMock = XMock::of(UserAdminMapper::class);
        $mapperFactoryMock = XMock::of(MapperFactory::class);
        $mapperFactoryMock
            ->expects($this->any())
            ->method('__get')
            ->will($this->returnValue($userAdminMapperMock));
        $serviceManager->setService(MapperFactory::class, $mapperFactoryMock);
        $restClientMock = XMock::of(Client::class);
        $serviceManager->setService(Client::class, $restClientMock);
        $authorisationService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $serviceManager->setService("AuthorisationService", $authorisationService);

        // when
        $result = $sut->createService($serviceManager);

        // then
        $this->assertInstanceOf(HelpdeskAccountAdminService::class, $result);
    }
}
