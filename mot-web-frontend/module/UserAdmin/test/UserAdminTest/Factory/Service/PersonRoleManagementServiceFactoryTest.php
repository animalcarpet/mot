<?php

namespace UserAdminTest\Factory\Service;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use CoreTest\Service\StubCatalogService;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Factory\Service\PersonRoleManagementServiceFactory;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\ServiceManager\ServiceManager;
use DvsaCommon\Auth\MotIdentityProviderInterface;

class PersonRoleManagementServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();

        $authorisationService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $motIdentityProvider = XMock::of(MotIdentityProviderInterface::class);
        $client = XMock::of(HttpRestJsonClient::class);
        $catalogService = new StubCatalogService();

        $serviceManager->setService('MotIdentityProvider', $motIdentityProvider);
        $serviceManager->setService('AuthorisationService', $authorisationService);
        $serviceManager->setService(HttpRestJsonClient::class, $client);
        $serviceManager->setService('CatalogService', $catalogService);

        $factory = new PersonRoleManagementServiceFactory();

        $this->assertInstanceOf(
            PersonRoleManagementService::class,
            $factory->createService($serviceManager)
        );
    }
}
